<?php

namespace Drupal\ksa_editor\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\layout_builder\SectionStorageInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\Routing\Route;

/** Limits content editors to text/image blocks on editable pages. */
final class EditorLayoutAccess implements AccessInterface {

  public const BLOCK_TYPES = ['basic', 'contact', 'photo', 'media_strip', 'hero', 'info_card', 'text_media'];

  public function access(SectionStorageInterface $section_storage, AccountInterface $account, Route $route, RouteMatchInterface $route_match): AccessResult {
    if ($account->hasPermission('configure any layout')) {
      return AccessResult::allowed()->cachePerPermissions();
    }
    $operation = $route->getRequirement('_ksa_editor_layout_access');
    if ($operation === 'reset' || $section_storage->getStorageType() !== 'overrides') {
      return AccessResult::forbidden()->cachePerPermissions();
    }
    $entity = $section_storage->getContextValue('entity');
    if (!$entity instanceof NodeInterface || $entity->bundle() !== 'page') {
      return AccessResult::forbidden()->setCacheMaxAge(0);
    }
    $access = AccessResult::allowedIfHasPermission($account, 'edit any page content')
      ->andIf($entity->access('update', $account, TRUE));
    if ($operation === 'block') {
      try {
        $component = $section_storage->getSection((int) $route_match->getParameter('delta'))
          ->getComponent($route_match->getParameter('uuid'));
        $allowed = in_array($component->getPluginId(), array_map(static fn ($type) => 'inline_block:' . $type, self::BLOCK_TYPES), TRUE);
        $access = $access->andIf(AccessResult::allowedIf($allowed));
      }
      catch (\InvalidArgumentException|\OutOfBoundsException $e) {
        return AccessResult::forbidden()->setCacheMaxAge(0);
      }
    }
    // Components can be changed in the per-user layout tempstore.
    return $access->setCacheMaxAge(0);
  }

}
