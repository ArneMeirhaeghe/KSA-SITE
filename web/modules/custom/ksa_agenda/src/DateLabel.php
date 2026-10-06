<?php

declare(strict_types=1);

namespace Drupal\ksa_agenda;

/**
 * Writes an event's date as Dutch text, e.g. "29 – 31 januari 2027".
 *
 * Month names are fixed here instead of coming from interface translation,
 * so the label never falls back to English.
 */
final class DateLabel {

  private const MONTHS = [
    1 => 'januari', 'februari', 'maart', 'april', 'mei', 'juni',
    'juli', 'augustus', 'september', 'oktober', 'november', 'december',
  ];

  /**
   * Formats one event array from EventParser.
   */
  public static function format(array $event): string {
    [$sy, $sm, $sd] = array_map('intval', explode('-', $event['start']));
    [$ey, $em, $ed] = array_map('intval', explode('-', $event['end']));
    $start_time = $event['start_time'] ?? NULL;
    $end_time = $event['end_time'] ?? NULL;

    // Timed events: date plus hours.
    if ($start_time !== NULL) {
      $start = self::day($sd, $sm, $sy) . ', ' . $start_time;
      if ($event['start'] !== $event['end']) {
        return $start . ' – ' . self::day($ed, $em, $ey) . ', ' . $end_time;
      }
      return $end_time && $end_time !== $start_time ? $start . ' – ' . $end_time : $start;
    }

    // All-day events: shorten the range where the month or year repeats.
    if ($event['start'] === $event['end']) {
      return self::day($sd, $sm, $sy);
    }
    return self::day($sd, $sm, $sy) . ' – ' . self::day($ed, $em, $ey);
  }

  /**
   * One day, e.g. "18 oktober 2026".
   */
  private static function day(int $d, int $m, int $y): string {
    $weekday = ['zo', 'ma', 'di', 'wo', 'do', 'vr', 'za'][(int) (new \DateTimeImmutable(sprintf('%04d-%02d-%02d', $y, $m, $d)))->format('w')];
    return $weekday . ' ' . $d . ' ' . self::MONTHS[$m] . ' ' . $y;
  }

}
