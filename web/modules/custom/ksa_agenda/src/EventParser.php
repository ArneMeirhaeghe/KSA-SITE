<?php

declare(strict_types=1);

namespace Drupal\ksa_agenda;

use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VEvent;
use Sabre\VObject\Reader;

/**
 * Turns an iCal feed into a flat, sorted list of events.
 *
 * Recurring events (RRULE, EXDATE) are expanded by sabre/vobject. All dates
 * and times are expressed in Europe/Brussels.
 *
 * Each event is an array with:
 * - id: short stable key, unique per occurrence of a recurring event.
 * - start, end: 'Y-m-d'. The end date is inclusive.
 * - start_time, end_time: 'H:i', or NULL for all-day events.
 * - title, location: plain text ('' when missing).
 */
final class EventParser {

  public const TIMEZONE = 'Europe/Brussels';

  /**
   * Parses and expands all events that touch the given window.
   *
   * @return list<array{id: string, start: string, end: string, start_time: ?string, end_time: ?string, title: string, location: string}>
   */
  public static function parse(string $ics, \DateTimeInterface $from, \DateTimeInterface $to): array {
    $tz = new \DateTimeZone(self::TIMEZONE);
    $calendar = Reader::read($ics, Reader::OPTION_FORGIVING);
    if (!$calendar instanceof VCalendar) {
      return [];
    }

    $events = [];
    foreach ($calendar->expand($from, $to, $tz)->select('VEVENT') as $vevent) {
      // Filter after expansion: cancelled overrides must still suppress their
      // original occurrence, rather than making that occurrence reappear.
      if ($vevent instanceof VEvent && strtoupper((string) ($vevent->STATUS ?? '')) !== 'CANCELLED') {
        $events[] = self::toArray($vevent, $tz);
      }
    }

    usort($events, static fn (array $a, array $b) => [$a['start'], $a['start_time'] ?? '', $a['title']] <=> [$b['start'], $b['start_time'] ?? '', $b['title']]);
    return $events;
  }

  /**
   * Keeps events that have not ended before the given day.
   *
   * Filtering on the end date keeps a camp that is still going on.
   */
  public static function upcoming(array $events, string $today): array {
    return array_values(array_filter($events, static fn (array $e) => $e['end'] >= $today));
  }

  /**
   * Flattens one expanded occurrence.
   */
  private static function toArray(VEvent $vevent, \DateTimeZone $tz): array {
    $all_day = !$vevent->DTSTART->hasTime();
    // All-day dates are floating: read them without shifting the day.
    $start = $all_day
      ? \DateTimeImmutable::createFromFormat('!Ymd', substr((string) $vevent->DTSTART->getValue(), 0, 8), $tz)
      : \DateTimeImmutable::createFromInterface($vevent->DTSTART->getDateTime())->setTimezone($tz);

    $end = $start;
    if (isset($vevent->DTEND)) {
      $end = $all_day
        ? \DateTimeImmutable::createFromFormat('!Ymd', substr((string) $vevent->DTEND->getValue(), 0, 8), $tz)
        : \DateTimeImmutable::createFromInterface($vevent->DTEND->getDateTime())->setTimezone($tz);
    }
    elseif (isset($vevent->DURATION)) {
      $end = $start->add($vevent->DURATION->getDateInterval());
    }

    // An all-day DTEND is exclusive: 4 to 7 October means up to the 6th.
    if ($all_day && $end > $start) {
      $end = $end->modify('-1 day');
    }

    $uid = (string) ($vevent->UID ?? '');
    return [
      'id' => substr(hash('sha256', $uid . '|' . $start->format('c')), 0, 16),
      'start' => $start->format('Y-m-d'),
      'end' => $end->format('Y-m-d'),
      'start_time' => $all_day ? NULL : $start->format('H:i'),
      'end_time' => $all_day ? NULL : $end->format('H:i'),
      'title' => trim((string) ($vevent->SUMMARY ?? '')),
      'location' => trim((string) ($vevent->LOCATION ?? '')),
    ];
  }

}
