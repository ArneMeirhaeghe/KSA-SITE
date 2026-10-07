<?php

/**
 * Read-only checks of active editor/visitor permissions and layout routes.
 * Run: ddev drush php:script scripts/check-editor-access.php
 */

use Drupal\Core\Session\AnonymousUserSession;
use Drupal\Core\Session\UserSession;
use Drupal\node\Entity\Node;

$account = new UserSession(['uid' => 999999, 'roles' => ['authenticated', 'content_editor']]);
$visitor = new AnonymousUserSession();
$checks = [];
$manager = \Drupal::entityTypeManager();
$handler = $manager->getAccessControlHandler('node');
foreach (['page', 'article', 'team', 'leader'] as $bundle) {
  $checks["create $bundle policy"] = $handler->createAccess($bundle, $account) === in_array($bundle, ['article', 'leader'], TRUE);
  $checks["visitor cannot create $bundle"] = !$handler->createAccess($bundle, $visitor);
  $node = Node::create(['type' => $bundle, 'title' => 'Access check', 'uid' => 1, 'status' => 0]);
  $grants = view_unpublished_node_grants($account, 'view');
  $checks["unpublished $bundle grant"] = isset($grants["view_unpublished_{$bundle}_content"]);
  $checks["edit another editor's $bundle"] = $node->access('update', $account);
  $checks["cannot delete $bundle"] = !$node->access('delete', $account);
  $checks["can publish $bundle"] = $node->get('status')->access('edit', $account);
  $checks["visitor cannot view unpublished $bundle"] = !$node->access('view', $visitor);
  $checks["visitor cannot edit $bundle"] = !$node->access('update', $visitor);
}
foreach (['image', 'document', 'audio', 'video', 'remote_video'] as $bundle) {
  $checks["create $bundle media policy"] = $manager->getAccessControlHandler('media')->createAccess($bundle, $account) === in_array($bundle, ['image', 'document'], TRUE);
  $checks["visitor cannot upload $bundle"] = !$manager->getAccessControlHandler('media')->createAccess($bundle, $visitor);
}
foreach (['administer users', 'administer permissions', 'administer modules', 'import configuration', 'administer ksa agenda', 'configure any layout', 'administer block content', 'delete own files', 'delete any file', 'administer url aliases', 'administer nodes', 'use text format full_html'] as $permission) {
  $checks["no $permission"] = !$account->hasPermission($permission);
}
foreach (['field_style', 'field_background'] as $name) {
  $block = $manager->getStorage('block_content')->create(['type' => 'text_media']);
  $checks["locked block field $name"] = !$block->get($name)->access('edit', $account);
}
$access = \Drupal::service('access_manager');
foreach (['user.admin_index', 'user.admin_permissions', 'system.modules_list', 'system.admin_config', 'ksa_agenda.settings', 'entity.node.delete_form'] as $route) {
  $checks["blocked route $route"] = !$access->checkNamedRoute($route, $route === 'entity.node.delete_form' ? ['node' => 33] : [], $account);
}
$switcher = \Drupal::service('account_switcher');
$switcher->switchTo($account);
try {
  foreach ($manager->getStorage('node')->loadMultiple() as $node) {
    $checks["editor can view existing node {$node->id()}"] = $node->access('view', $account);
    $checks["visitor visibility node {$node->id()}"] = $node->access('view', $visitor) === $node->isPublished();
  }
  $form = \Drupal::service('entity.form_builder')->getForm(Node::load(10), 'default');
  foreach (['uid', 'created', 'langcode', 'path', 'revision_information'] as $key) {
    $checks["leader form hides $key"] = !isset($form[$key]) || ($form[$key]['#access'] ?? TRUE) === FALSE;
  }
  foreach (['title', 'status', 'field_photo', 'field_phone', 'field_team'] as $key) {
    $checks["leader form exposes $key"] = isset($form[$key]) && ($form[$key]['#access'] ?? TRUE) !== FALSE;
  }
  $page_form = \Drupal::service('entity.form_builder')->getForm(Node::load(33), 'default');
  $checks['editor cannot edit SEO metadata'] = !Node::load(33)->get('field_metatags')->access('edit', $account);
  foreach (['field_metatags', 'simple_sitemap', 'simple_sitemap_regenerate_now', 'url_redirects'] as $key) {
    $checks["page form hides $key"] = !isset($page_form[$key]) || ($page_form[$key]['#access'] ?? TRUE) === FALSE;
  }
  $pages = $manager->getStorage('node')->loadByProperties(['type' => 'page']);
  foreach ($pages as $page) {
    $checks["page {$page->id()} content editor"] = $access->checkNamedRoute('layout_builder.overrides.node.view', ['node' => $page->id()], $account);
    $checks["page {$page->id()} reset blocked"] = !$access->checkNamedRoute('layout_builder.overrides.node.revert', ['node' => $page->id()], $account);
    $checks["visitor cannot access page {$page->id()} editor"] = !$access->checkNamedRoute('layout_builder.overrides.node.view', ['node' => $page->id()], $visitor);
    $params = ['section_storage_type' => 'overrides', 'section_storage' => 'node.' . $page->id(), 'delta' => 0, 'region' => 'content'];
    foreach (['layout_builder.choose_section', 'layout_builder.choose_block', 'layout_builder.remove_section'] as $route) {
      $checks["page {$page->id()} blocked $route"] = !$access->checkNamedRoute($route, $params, $account);
    }
    foreach ($page->get('layout_builder__layout')->getSections() as $delta => $section) {
      foreach ($section->getComponents() as $component) {
        $params['delta'] = $delta;
        $params['region'] = $component->getRegion();
        $params['uuid'] = $component->getUuid();
        $plugin = $component->getPluginId();
        $expected = in_array($plugin, array_map(static fn ($type) => 'inline_block:' . $type, \Drupal\ksa_editor\Access\EditorLayoutAccess::BLOCK_TYPES), TRUE);
        $checks["page {$page->id()} edit $plugin {$params['uuid']}"] = $access->checkNamedRoute('layout_builder.update_block', $params, $account) === $expected;
        foreach (['layout_builder.remove_block', 'layout_builder.move_block_form'] as $route) {
          $checks["page {$page->id()} blocked $route {$params['uuid']}"] = !$access->checkNamedRoute($route, $params, $account);
        }
      }
    }
  }
  $home = \Drupal::classResolver(\Drupal\ksa_editor\Controller\EditorController::class)->home();
  $checks['dashboard has five task groups'] = count($home['tasks']['#items']) === 5;
  $navigation = \Drupal::service('navigation.menu_tree');
  $tree = $navigation->load('content', new \Drupal\Core\Menu\MenuTreeParameters());
  ksa_editor_navigation_menu_link_tree_alter($tree);
  $checks['navigation contains only the editor start link'] = count($tree) === 1 && reset($tree)->link->getPluginId() === 'ksa_editor.home';
}
finally {
  $switcher->switchBack();
}
$checks['registration restricted to administrator'] = \Drupal::config('user.settings')->get('register') === 'admin_only';
$pdf = \Drupal::config('field.field.media.document.field_media_document');
$checks['PDF-only upload'] = $pdf->get('settings.file_extensions') === 'pdf';
$checks['20 MB upload limit'] = $pdf->get('settings.max_filesize') === '20 MB';
$failed = 0;
foreach ($checks as $label => $passed) {
  if (!$passed) {
    printf("FAIL %s\n", $label);
    $failed++;
  }
}
printf("%d/%d access checks passed. No users or content were saved.\n", count($checks) - $failed, count($checks));
if ($failed) {
  throw new \RuntimeException("$failed access checks failed.");
}
