<?php

declare(strict_types=1);

namespace Drupal\ksa_agenda;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\State\StateInterface;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Fetches the public Google Calendar feed and keeps the last good result.
 *
 * Runs on cron and when the settings are saved, never during a page view.
 * The result lives in State, so a cache rebuild does not wipe it, and a
 * broken feed leaves the previous events in place.
 */
final class AgendaFetcher {

  public const STATE_KEY = 'ksa_agenda.events';
  public const CACHE_TAG = 'ksa_agenda';

  public function __construct(
    private readonly ClientInterface $httpClient,
    private readonly StateInterface $state,
    private readonly ConfigFactoryInterface $configFactory,
    private readonly CacheTagsInvalidatorInterface $cacheTagsInvalidator,
    private readonly LoggerInterface $logger,
    private readonly TimeInterface $time,
  ) {}

  /**
   * Fetches the feed again.
   *
   * @return int|null
   *   The number of upcoming events, or NULL when the feed could not be read.
   */
  public function refresh(): ?int {
    $calendar = $this->calendar();
    if ($calendar === NULL) {
      $this->state->delete(self::STATE_KEY);
      $this->state->delete('ksa_agenda.last_failure');
      $this->cacheTagsInvalidator->invalidateTags([self::CACHE_TAG]);
      return 0;
    }

    $today = $this->today();
    try {
      $response = $this->httpClient->request('GET', $calendar->feedUrl(), ['timeout' => 10]);
      $events = EventParser::parse((string) $response->getBody(), $today->modify('-60 days'), $today->modify('+400 days'));
    }
    catch (\Throwable $e) {
      $this->state->set('ksa_agenda.last_failure', $this->time->getRequestTime());
      $this->logger->warning('Agenda niet opgehaald: @message', ['@message' => $e->getMessage()]);
      return NULL;
    }

    $this->state->set(self::STATE_KEY, [
      'calendar_id' => $calendar->id,
      'fetched' => $this->time->getRequestTime(),
      'events' => $events,
    ]);
    $this->state->delete('ksa_agenda.last_failure');
    // The daily middleware separately expires date-sensitive page output.
    $this->cacheTagsInvalidator->invalidateTags([self::CACHE_TAG]);
    return count(EventParser::upcoming($events, $today->format('Y-m-d')));
  }

  /**
   * Upcoming events of the configured calendar, from the last good fetch.
   */
  public function upcoming(): array {
    $stored = $this->state->get(self::STATE_KEY);
    $calendar = $this->calendar();
    // Stored events of a previous calendar do not belong on the page.
    if (!$calendar || ($stored['calendar_id'] ?? NULL) !== $calendar->id) {
      return [];
    }
    return EventParser::upcoming($stored['events'] ?? [], $this->today()->format('Y-m-d'));
  }

  /**
   * One upcoming event by its id, or NULL.
   */
  public function find(string $id): ?array {
    foreach ($this->upcoming() as $event) {
      if (($event['id'] ?? NULL) === $id) {
        return $event;
      }
    }
    return NULL;
  }

  /** Synchronisation status for the administrative settings form. */
  public function status(): array {
    $stored = $this->state->get(self::STATE_KEY, []);
    $matching = ($stored['calendar_id'] ?? NULL) === $this->calendar()?->id;
    return [
      'fetched' => $matching ? ($stored['fetched'] ?? NULL) : NULL,
      'failure' => $this->state->get('ksa_agenda.last_failure'),
    ];
  }

  /**
   * The configured calendar, or NULL when none is set.
   */
  public function calendar(): ?CalendarId {
    try {
      return CalendarId::fromInput((string) $this->configFactory->get('ksa_agenda.settings')->get('calendar_id'));
    }
    catch (\InvalidArgumentException) {
      return NULL;
    }
  }

  /**
   * Midnight today in Brussels.
   */
  private function today(): \DateTimeImmutable {
    return (new \DateTimeImmutable('@' . $this->time->getRequestTime()))
      ->setTimezone(new \DateTimeZone(EventParser::TIMEZONE))
      ->setTime(0, 0);
  }

}
