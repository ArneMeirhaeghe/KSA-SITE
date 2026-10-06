<?php

declare(strict_types=1);

namespace Drupal\ksa_agenda\StackMiddleware;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\State\StateInterface;
use Drupal\ksa_agenda\AgendaFetcher;
use Drupal\ksa_agenda\EventParser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/** Invalidates date-sensitive agenda output before Internal Page Cache. */
final class AgendaDay implements HttpKernelInterface {

  public function __construct(
    private readonly HttpKernelInterface $httpKernel,
    private readonly StateInterface $state,
    private readonly CacheTagsInvalidatorInterface $invalidator,
    private readonly TimeInterface $time,
  ) {}

  public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = TRUE): Response {
    if ($type === self::MAIN_REQUEST) {
      $day = (new \DateTimeImmutable('@' . $this->time->getRequestTime()))
        ->setTimezone(new \DateTimeZone(EventParser::TIMEZONE))->format('Y-m-d');
      if ($this->state->get('ksa_agenda.render_day') !== $day) {
        $this->invalidator->invalidateTags([AgendaFetcher::CACHE_TAG]);
        $this->state->set('ksa_agenda.render_day', $day);
      }
    }
    return $this->httpKernel->handle($request, $type, $catch);
  }

}
