<?php

declare(strict_types=1);

namespace Drupal\ksa_agenda\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\ksa_agenda\AgendaFetcher;

/**
 * Hook implementations for KSA Agenda.
 */
final class AgendaHooks {

  public function __construct(
    private readonly AgendaFetcher $fetcher,
  ) {}

  /**
   * Implements hook_cron().
   */
  #[Hook('cron')]
  public function cron(): void {
    $this->fetcher->refresh();
  }

}
