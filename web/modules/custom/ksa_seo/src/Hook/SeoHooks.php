<?php

declare(strict_types=1);

namespace Drupal\ksa_seo\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Hook\Order\Order;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Hook implementations for KSA SEO.
 */
final class SeoHooks {

  public function __construct(
    private readonly RouteMatchInterface $routeMatch,
    private readonly RequestStack $requestStack,
  ) {}

  /**
   * Implements hook_metatags_alter().
   *
   * Gives every page of the news archive its own canonical URL and title.
   * Runs last: metatag_views adds the title of the View in this same hook.
   */
  #[Hook('metatags_alter', order: Order::Last)]
  public function newsArchiveTags(array &$metatags, array &$context): void {
    if ($this->routeMatch->getRouteName() !== 'view.news.page_1') {
      return;
    }
    $value = $this->requestStack->getCurrentRequest()->query->all()['page'] ?? '0';
    $page = is_scalar($value) ? max(0, (int) $value) : 0;
    $url = Url::fromRoute('view.news.page_1', [], [
      'absolute' => TRUE,
      'query' => $page > 0 ? ['page' => $page] : [],
    ])->toString();
    $metatags['canonical_url'] = $url;
    $metatags['og_url'] = $url;
    // The first page is simply "Nieuws": drop the " — pagina 1" part. This
    // works for a title of the form "Name — pagina [token] | Site", as set in
    // the View (Meta tags): a dash with a space on both sides, then the part
    // with the [current-page:page-number] token. Other forms stay as they are.
    if ($page === 0 && isset($metatags['title'])) {
      $metatags['title'] = preg_replace('/\s+[—–-]\s+[^|]*\[current-page:page-number\][^|]*(?=\s\||$)/u', '', $metatags['title']);
    }
  }

}
