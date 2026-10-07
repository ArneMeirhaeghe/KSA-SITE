<?php

namespace Drupal\ksa_editor\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

/** Adds project restrictions to the existing Layout Builder access checks. */
final class EditorRoutes extends RouteSubscriberBase {

  public static function getSubscribedEvents(): array {
    // Layout Builder creates its entity routes at priority -110.
    return [RoutingEvents::ALTER => ['onAlterRoutes', -200]];
  }

  protected function alterRoutes(RouteCollection $collection): void {
    foreach ($collection as $name => $route) {
      if ($name === 'layout_builder.update_block') {
        $route->setRequirement('_ksa_editor_layout_access', 'block');
      }
      elseif (str_starts_with($name, 'layout_builder.overrides.')) {
        $route->setRequirement('_ksa_editor_layout_access', str_ends_with($name, '.revert') ? 'reset' : 'page');
      }
    }
  }

}
