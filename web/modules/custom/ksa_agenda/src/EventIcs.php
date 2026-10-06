<?php

declare(strict_types=1);

namespace Drupal\ksa_agenda;

use Sabre\VObject\Component\VCalendar;

/**
 * Builds an .ics file with one event, to add it to a personal calendar.
 *
 * Phones open such a file straight in their calendar app.
 */
final class EventIcs {

  /**
   * Returns the iCal text for one event array from EventParser.
   */
  public static function build(array $event): string {
    $tz = new \DateTimeZone(EventParser::TIMEZONE);
    $calendar = new VCalendar(['PRODID' => '-//KSA Deinze-Astene//Agenda//NL']);

    $properties = [
      'UID' => $event['id'] . '@ksadeinze.be',
      'SUMMARY' => $event['title'],
      'DTSTAMP' => new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
    ];
    if ($event['location'] !== '') {
      $properties['LOCATION'] = $event['location'];
    }
    $vevent = $calendar->add('VEVENT', $properties);

    if ($event['start_time'] === NULL) {
      // All-day: DTEND is the day after the last day.
      $end = (new \DateTimeImmutable($event['end'], $tz))->modify('+1 day');
      $vevent->add('DTSTART', str_replace('-', '', $event['start']), ['VALUE' => 'DATE']);
      $vevent->add('DTEND', $end->format('Ymd'), ['VALUE' => 'DATE']);
    }
    else {
      $vevent->add('DTSTART', new \DateTimeImmutable($event['start'] . ' ' . $event['start_time'], $tz));
      $vevent->add('DTEND', new \DateTimeImmutable($event['end'] . ' ' . $event['end_time'], $tz));
    }

    return $calendar->serialize();
  }

}
