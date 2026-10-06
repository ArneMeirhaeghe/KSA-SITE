<?php

/**
 * Read-only access checks against the active Drupal configuration.
 * Run: ddev drush php:script scripts/check-editor-access.php
 */

use Drupal\Core\Session\UserSession;
use Drupal\node\Entity\Node;

$account = new UserSession(['uid' => 999999, 'roles' => ['authenticated', 'content_editor']]);
$checks = [];
$handler = \Drupal::entityTypeManager()->getAccessControlHandler('node');
foreach (['page', 'article', 'team', 'leader'] as $bundle) {
  $checks["create $bundle"] = $handler->createAccess($bundle, $account);
}
foreach ([33, 30, 2, 10] as $id) {
  $node = Node::load($id);
  $checks['edit ' . $node->bundle()] = $node->access('update', $account);
  $checks['cannot delete ' . $node->bundle()] = !$node->access('delete', $account);
}
foreach (['image', 'document'] as $bundle) {
  $checks["create $bundle media"] = \Drupal::entityTypeManager()->getAccessControlHandler('media')->createAccess($bundle, $account);
}
foreach (['administer users', 'administer permissions', 'administer modules', 'import configuration', 'administer ksa agenda', 'configure any layout', 'administer block content'] as $permission) {
  $checks["no $permission"] = !$account->hasPermission($permission);
}
$failed = 0;
foreach ($checks as $label => $passed) {
  printf("%s %s\n", $passed ? 'OK' : 'FAIL', $label);
  $failed += !$passed;
}
if ($failed) {
  throw new \RuntimeException("$failed access checks failed.");
}
printf("%d access checks passed. No users or content were saved.\n", count($checks));
