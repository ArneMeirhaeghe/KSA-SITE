<?php

/**
 * Temporary check for Drupal\ksa_agenda\EventIcs (no PHPUnit installed).
 *
 * Run inside DDEV:
 *   ddev exec php scripts/check-event-ics.php
 */

declare(strict_types=1);

require '/var/www/html/vendor/autoload.php';
$file = '/var/www/html/web/modules/custom/ksa_agenda/src/EventIcs.php';
if (!is_file($file)) {
  fwrite(STDERR, "RED: $file does not exist.\n");
  exit(1);
}
require $file;
require '/var/www/html/web/modules/custom/ksa_agenda/src/EventParser.php';

use Drupal\ksa_agenda\EventIcs;
use Drupal\ksa_agenda\EventParser;

$events = [
  'hele dag' => ['id' => 'a1', 'start' => '2026-10-24', 'end' => '2026-10-24', 'start_time' => NULL, 'end_time' => NULL, 'title' => 'Stuntdag, voor iedereen', 'location' => 'Sporthal Deinze'],
  'meerdere dagen' => ['id' => 'a2', 'start' => '2027-04-30', 'end' => '2027-05-02', 'start_time' => NULL, 'end_time' => NULL, 'title' => 'Gamel (Knim)', 'location' => ''],
  'met uren, zomer' => ['id' => 'a3', 'start' => '2026-10-10', 'end' => '2026-10-10', 'start_time' => '14:00', 'end_time' => '17:00', 'title' => 'Ronde Dolfijntjes', 'location' => 'Lokaal'],
  'met uren, winter' => ['id' => 'a4', 'start' => '2026-11-10', 'end' => '2026-11-10', 'start_time' => '19:00', 'end_time' => '21:00', 'title' => 'Kerstrozen verkoop', 'location' => ''],
];

$tz = new \DateTimeZone('Europe/Brussels');
$pass = 0;
$total = 0;
foreach ($events as $label => $event) {
  $ics = EventIcs::build($event);
  $back = EventParser::parse($ics, new \DateTimeImmutable('2026-01-01', $tz), new \DateTimeImmutable('2028-01-01', $tz))[0] ?? [];
  unset($back['id'], $event['id']);
  $total++;
  $ok = $back === $event;
  $pass += (int) $ok;
  printf("%s %-18s %s\n", $ok ? 'OK  ' : 'FAIL', $label, $ok ? 'terug gelezen als dezelfde activiteit' : json_encode($back));
}

$one = EventIcs::build($events['hele dag']);
foreach ([
  'één VEVENT' => substr_count($one, 'BEGIN:VEVENT') === 1,
  'CRLF-regeleinden' => str_contains($one, "\r\n"),
  'UID met id' => str_contains($one, 'UID:a1@ksadeinze.be'),
] as $label => $ok) {
  $total++;
  $pass += (int) $ok;
  printf("%s %-18s\n", $ok ? 'OK  ' : 'FAIL', $label);
}

echo "\n$pass of $total checks passed.\n";
exit($pass === $total ? 0 : 1);
