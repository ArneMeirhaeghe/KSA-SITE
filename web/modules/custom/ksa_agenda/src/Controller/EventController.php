<?php

declare(strict_types=1);

namespace Drupal\ksa_agenda\Controller;

use Drupal\Core\Cache\CacheableResponse;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\ksa_agenda\AgendaFetcher;
use Drupal\ksa_agenda\EventIcs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Serves one event as an .ics file, to add it to a personal calendar.
 */
final class EventController implements ContainerInjectionInterface {

  public function __construct(
    private readonly AgendaFetcher $fetcher,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static($container->get('ksa_agenda.fetcher'));
  }

  /**
   * Returns the .ics file, or a 404 for an unknown or past event.
   */
  public function ics(string $event): CacheableResponse {
    $found = $this->fetcher->find($event);
    if ($found === NULL) {
      throw new NotFoundHttpException();
    }

    $slug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($found['title'])), '-');
    $response = new CacheableResponse(EventIcs::build($found), 200, [
      'Content-Type' => 'text/calendar; charset=utf-8',
      'Content-Disposition' => sprintf('attachment; filename="ksa-%s-%s.ics"', $slug ?: 'activiteit', $found['start']),
    ]);
    $response->getCacheableMetadata()->addCacheTags([AgendaFetcher::CACHE_TAG, 'config:ksa_agenda.settings']);
    return $response;
  }

}
