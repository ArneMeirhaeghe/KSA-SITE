<?php

namespace Drupal\ksa_editor\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

/** Small task-oriented entry point for the site's editors. */
final class EditorController extends ControllerBase {

  private const TITLES = ['page' => 'Pagina’s', 'article' => 'Nieuws', 'leader' => 'Leiding', 'team' => 'Ploegen en rondeboekjes'];

  public function title(string $bundle): string {
    return self::TITLES[$bundle];
  }

  public function home(): array {
    $items = [];
    foreach (self::TITLES as $bundle => $title) {
      if ($this->currentUser()->hasPermission("edit any $bundle content")) {
        $items[] = Link::createFromRoute($title, 'ksa_editor.content', ['bundle' => $bundle])->toRenderable();
      }
    }
    if ($this->currentUser()->hasPermission('access media overview')) {
      $items[] = Link::createFromRoute('Foto’s en PDF’s', 'ksa_editor.media')->toRenderable();
    }
    return [
      '#cache' => ['contexts' => ['user.permissions']],
      'intro' => ['#markup' => '<p>Kies wat je wilt aanpassen. Je wijzigingen verschijnen na het opslaan op de website.</p>'],
      'tasks' => ['#theme' => 'item_list', '#items' => $items, '#attributes' => ['class' => ['ksa-editor-tasks']]],
      'agenda' => ['#markup' => '<p>Activiteiten en datums pas je aan in de gedeelde Google Agenda. Een nieuw rondeboekje koppel je onder <strong>Ploegen en rondeboekjes</strong>.</p>'],
      'site' => Link::createFromRoute('Website bekijken', '<front>')->toRenderable(),
      '#attached' => ['library' => ['ksa_editor/editor']],
    ];
  }

  public function content(string $bundle): array {
    $storage = $this->entityTypeManager()->getStorage('node');
    $ids = $storage->getQuery()->accessCheck(TRUE)->condition('type', $bundle)->sort('title')->execute();
    $rows = [];
    foreach ($storage->loadMultiple($ids) as $node) {
      if (!$node->access('update', $this->currentUser())) {
        continue;
      }
      $edit = $bundle === 'page'
        ? Url::fromRoute('layout_builder.overrides.node.view', ['node' => $node->id()])
        : $node->toUrl('edit-form');
      $edit->setOption('query', ['destination' => '/admin/leiding/' . $bundle]);
      $actions = [Link::fromTextAndUrl('Bewerken', $edit)->toRenderable()];
      if ($bundle === 'page') {
        $actions[] = Link::fromTextAndUrl('Titel en intro', $node->toUrl('edit-form', ['query' => ['destination' => '/admin/leiding/page']]))->toRenderable();
      }
      $rows[] = [
        $node->label(),
        $node->isPublished() ? 'Zichtbaar' : 'Verborgen',
        ['data' => ['#theme' => 'item_list', '#items' => $actions, '#attributes' => ['class' => ['ksa-editor-actions']]]],
      ];
    }
    $build = [
      '#cache' => ['max-age' => 0],
      '#attached' => ['library' => ['ksa_editor/editor']],
      'back' => Link::createFromRoute('← Site beheren', 'ksa_editor.home')->toRenderable(),
    ];
    if (in_array($bundle, ['article', 'leader'], TRUE) && $this->entityTypeManager()->getAccessControlHandler('node')->createAccess($bundle)) {
      $build['add'] = Link::createFromRoute($bundle === 'leader' ? 'Nieuwe leider' : 'Nieuw nieuwsbericht', 'entity.node.add_form', ['node_type' => $bundle], ['attributes' => ['class' => ['button', 'button--primary']], 'query' => ['destination' => '/admin/leiding/' . $bundle]])->toRenderable();
    }
    $build['table'] = ['#type' => 'table', '#header' => ['Naam', 'Op de website', ''], '#rows' => $rows, '#empty' => 'Er is nog geen inhoud.'];
    return $build;
  }

  public function media(): array {
    $storage = $this->entityTypeManager()->getStorage('media');
    $ids = $storage->getQuery()->accessCheck(TRUE)->condition('bundle', ['image', 'document'], 'IN')->sort('changed', 'DESC')->pager(25)->execute();
    $rows = [];
    foreach ($storage->loadMultiple($ids) as $media) {
      if (!$media->access('update', $this->currentUser())) {
        continue;
      }
      $rows[] = [
        $media->label(),
        $media->bundle() === 'image' ? 'Foto' : 'PDF',
        ['data' => Link::fromTextAndUrl('Bewerken', $media->toUrl('edit-form', ['query' => ['destination' => '/admin/leiding/media']]))->toRenderable()],
      ];
    }
    $build = ['#cache' => ['max-age' => 0], 'back' => Link::createFromRoute('← Site beheren', 'ksa_editor.home')->toRenderable()];
    foreach (['image' => 'Nieuwe foto', 'document' => 'Nieuwe PDF'] as $bundle => $label) {
      if ($this->entityTypeManager()->getAccessControlHandler('media')->createAccess($bundle)) {
        $build[$bundle] = Link::createFromRoute($label, 'entity.media.add_form', ['media_type' => $bundle], ['attributes' => ['class' => ['button']], 'query' => ['destination' => '/admin/leiding/media']])->toRenderable();
      }
    }
    $build['help'] = ['#markup' => '<p>Upload hier foto’s en PDF’s. Koppel een nieuw rondeboekje daarna bij de juiste ploeg. Bestanden zijn publiek toegankelijk.</p>'];
    $build['table'] = ['#type' => 'table', '#header' => ['Naam', 'Soort', ''], '#rows' => $rows, '#empty' => 'Er zijn nog geen bestanden.'];
    $build['pager'] = ['#type' => 'pager'];
    return $build;
  }

}
