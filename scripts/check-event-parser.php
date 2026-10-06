<?php

/**
 * Temporary check for Drupal\ksa_agenda\EventParser (no PHPUnit installed).
 *
 * Run inside DDEV:
 *   ddev exec php scripts/check-event-parser.php
 */

declare(strict_types=1);

require '/var/www/html/vendor/autoload.php';
$file = '/var/www/html/web/modules/custom/ksa_agenda/src/EventParser.php';
if (!is_file($file)) {
  fwrite(STDERR, "RED: $file does not exist.\n");
  exit(1);
}
require $file;

use Drupal\ksa_agenda\EventParser;

// A calendar like the leiding would really keep, not only all-day dates.
$ics = <<<'ICS'
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//KSA//fixture//NL
BEGIN:VTIMEZONE
TZID:Europe/Brussels
BEGIN:DAYLIGHT
TZOFFSETFROM:+0100
TZOFFSETTO:+0200
TZNAME:CEST
DTSTART:19700329T020000
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+0200
TZOFFSETTO:+0100
TZNAME:CET
DTSTART:19701025T030000
RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU
END:STANDARD
END:VTIMEZONE
BEGIN:VEVENT
UID:ronde@ksa
DTSTART;TZID=Europe/Brussels:20261003T140000
DTEND;TZID=Europe/Brussels:20261003T170000
RRULE:FREQ=WEEKLY;COUNT=5
EXDATE;TZID=Europe/Brussels:20261017T140000
SUMMARY:Ronde Dolfijntjes
LOCATION:Lokaal
END:VEVENT
BEGIN:VEVENT
UID:stunt@ksa
DTSTART;VALUE=DATE:20261024
DTEND;VALUE=DATE:20261025
SUMMARY:Stuntdag\, voor iedereen
LOCATION:Sporthal Deinze
END:VEVENT
BEGIN:VEVENT
UID:kamp@ksa
DTSTART;VALUE=DATE:20261004
DTEND;VALUE=DATE:20261007
SUMMARY:Weekend Sjo
END:VEVENT
BEGIN:VEVENT
UID:quiz@ksa
DTSTART:20261022T163000Z
DTEND:20261022T190000Z
SUMMARY:Quiz
END:VEVENT
BEGIN:VEVENT
UID:winter@ksa
DTSTART:20261110T180000Z
DTEND:20261110T200000Z
SUMMARY:Kerstrozen verkoop
END:VEVENT
BEGIN:VEVENT
UID:past@ksa
DTSTART;VALUE=DATE:20260901
DTEND;VALUE=DATE:20260902
SUMMARY:Voorbij
END:VEVENT
END:VCALENDAR
ICS;

$tz = new \DateTimeZone('Europe/Brussels');
$today = new \DateTimeImmutable('2026-10-05', $tz);
$all = EventParser::parse($ics, $today->modify('-60 days'), $today->modify('+400 days'));
$upcoming = EventParser::upcoming($all, '2026-10-05');

$by = static fn (string $title) => array_values(array_filter($upcoming, static fn ($e) => $e['title'] === $title));
$rondes = $by('Ronde Dolfijntjes');
$stunt = $by('Stuntdag, voor iedereen')[0] ?? NULL;
$kamp = $by('Weekend Sjo')[0] ?? NULL;
$quiz = $by('Quiz')[0] ?? NULL;
$winter = $by('Kerstrozen verkoop')[0] ?? NULL;

$checks = [
  // Weekly rondes: 3, 10, 17, 24, 31 Oct; 17 skipped (EXDATE), 3 is past.
  'rrule: 3 komende rondes' => [count($rondes), 3],
  'rrule: 17/10 overgeslagen' => [array_column($rondes, 'start'), ['2026-10-10', '2026-10-24', '2026-10-31']],
  'rrule: uur in Brussel' => [$rondes[0]['start_time'] ?? NULL, '14:00'],
  'all-day: geen uur' => [$stunt && array_key_exists('start_time', $stunt) ? $stunt['start_time'] : 'missing', NULL],
  'all-day: einde = zelfde dag' => [$stunt['end'] ?? NULL, '2026-10-24'],
  'komma in titel' => [$stunt['title'] ?? NULL, 'Stuntdag, voor iedereen'],
  'plaats' => [$stunt['location'] ?? NULL, 'Sporthal Deinze'],
  'kamp bezig blijft staan' => [$kamp['start'] ?? NULL, '2026-10-04'],
  'kamp einde inclusief' => [$kamp['end'] ?? NULL, '2026-10-06'],
  'UTC naar zomeruur' => [$quiz['start_time'] ?? NULL, '18:30'],
  'UTC naar winteruur' => [$winter['start_time'] ?? NULL, '19:00'],
  'voorbije activiteit weg' => [count($by('Voorbij')), 0],
  'gesorteerd op start' => [array_column($upcoming, 'start'), (function ($s) { sort($s); return $s; })(array_column($upcoming, 'start'))],
  'eerste = kamp (bezig)' => [$upcoming[0]['title'] ?? NULL, 'Weekend Sjo'],
  'elke activiteit heeft een id' => [count(array_filter(array_column($all, 'id'))), count($all)],
  'id is uniek, ook per ronde' => [count(array_unique(array_column($all, 'id'))), count($all)],
  'id is stabiel bij opnieuw lezen' => [array_column(EventParser::parse($ics, $today->modify('-60 days'), $today->modify('+400 days')), 'id'), array_column($all, 'id')],
];

// Cancellation must also work after recurrence expansion. An override removes
// exactly one occurrence; removing it before expansion would resurrect it.
$cancelled = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\n";
foreach (['day' => 'DTSTART;VALUE=DATE:20261018', 'timed' => 'DTSTART:20261018T120000Z', 'series' => "DTSTART:20261018T120000Z\r\nRRULE:FREQ=DAILY;COUNT=3"] as $uid => $start) {
  $cancelled .= "BEGIN:VEVENT\r\nUID:$uid\r\n$start\r\nSUMMARY:$uid\r\nSTATUS:CANCELLED\r\nEND:VEVENT\r\n";
}
$cancelled .= "BEGIN:VEVENT\r\nUID:override\r\nDTSTART:20261018T120000Z\r\nRRULE:FREQ=DAILY;COUNT=3\r\nSUMMARY:Active series\r\nEND:VEVENT\r\n";
$cancelled .= "BEGIN:VEVENT\r\nUID:override\r\nRECURRENCE-ID:20261019T120000Z\r\nDTSTART:20261019T120000Z\r\nSUMMARY:Cancelled occurrence\r\nSTATUS:CANCELLED\r\nEND:VEVENT\r\nEND:VCALENDAR";
$remaining = EventParser::parse($cancelled, $today, $today->modify('+30 days'));
$checks['cancelled day, timed and series excluded'] = [array_unique(array_column($remaining, 'title')), ['Active series']];
$checks['cancelled override does not resurrect'] = [array_column($remaining, 'start'), ['2026-10-18', '2026-10-20']];

$pass = 0;
foreach ($checks as $label => [$got, $expected]) {
  $ok = $got === $expected;
  $pass += (int) $ok;
  printf("%s %-28s %s\n", $ok ? 'OK  ' : 'FAIL', $label, $ok ? '' : 'expected ' . json_encode($expected) . ', got ' . json_encode($got));
}
echo "\n$pass of " . count($checks) . " checks passed.\n";
exit($pass === count($checks) ? 0 : 1);
