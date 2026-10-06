<?php

/**
 * Temporary check for Drupal\ksa_agenda\CalendarId (no PHPUnit installed yet).
 *
 * Run inside DDEV:
 *   ddev exec php scripts/check-calendar-id.php
 */

declare(strict_types=1);

$file = '/var/www/html/web/modules/custom/ksa_agenda/src/CalendarId.php';
if (!is_file($file)) {
  fwrite(STDERR, "RED: $file does not exist.\n");
  exit(1);
}
require $file;

use Drupal\ksa_agenda\CalendarId;

$id = '203665a2728b28ee0e0b9de9fc3accf24b87f3f5dfd9e87333dbdec5ef2094a3@group.calendar.google.com';
$enc = rawurlencode($id);

// [label, input, expected id OR exception code].
$cases = [
  ['raw id', $id, $id],
  ['raw id with spaces', "  $id \n", $id],
  ['url-encoded id', $enc, $id],
  ['embed url', "https://calendar.google.com/calendar/embed?src=$enc&ctz=Europe%2FBrussels", $id],
  ['iframe code', "<iframe src=\"https://calendar.google.com/calendar/embed?src=$enc&ctz=Europe%2FBrussels\" style=\"border: 0\" width=\"800\" height=\"600\"></iframe>", $id],
  ['public ics', "https://calendar.google.com/calendar/ical/$enc/public/basic.ics", $id],
  ['cid share link', 'https://calendar.google.com/calendar/u/0?cid=' . rtrim(base64_encode($id), '='), $id],
  ['personal gmail id', 'jan.peeters@gmail.com', 'jan.peeters@gmail.com'],
  ['secret ics', "https://calendar.google.com/calendar/ical/$enc/private-0000fake0000token0000/basic.ics", CalendarId::ERROR_SECRET],
  ['empty', '   ', CalendarId::ERROR_EMPTY],
  ['random text', 'onze kalender', CalendarId::ERROR_UNKNOWN],
  ['other website', 'https://www.ksadeinze.be/data', CalendarId::ERROR_UNKNOWN],
];

$pass = 0;
foreach ($cases as [$label, $input, $expected]) {
  try {
    $got = CalendarId::fromInput($input)->id;
  }
  catch (\InvalidArgumentException $e) {
    $got = $e->getCode();
  }
  $ok = $got === $expected;
  $pass += (int) $ok;
  printf("%s %-20s %s\n", $ok ? 'OK  ' : 'FAIL', $label, $ok ? '' : "expected " . var_export($expected, TRUE) . ", got " . var_export($got, TRUE));
}

// URL builders must round-trip to the same ID.
$c = CalendarId::fromInput($id);
$urls = [
  'feed url' => [$c->feedUrl(), "https://calendar.google.com/calendar/ical/$enc/public/basic.ics"],
  'feed round-trip' => [CalendarId::fromInput($c->feedUrl())->id, $id],
  'embed round-trip' => [CalendarId::fromInput($c->embedUrl())->id, $id],
  'subscribe round-trip' => [CalendarId::fromInput($c->subscribeUrl())->id, $id],
  'webcal url' => [$c->webcalUrl(), "webcal://calendar.google.com/calendar/ical/$enc/public/basic.ics"],
];
foreach ($urls as $label => [$got, $expected]) {
  $ok = $got === $expected;
  $pass += (int) $ok;
  printf("%s %-20s %s\n", $ok ? 'OK  ' : 'FAIL', $label, $ok ? '' : "expected $expected, got $got");
}

$total = count($cases) + count($urls);
echo "\n$pass of $total checks passed.\n";
exit($pass === $total ? 0 : 1);
