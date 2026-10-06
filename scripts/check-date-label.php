<?php

/**
 * Temporary check for Drupal\ksa_agenda\DateLabel (no PHPUnit installed).
 *
 * Run inside DDEV:
 *   ddev exec php scripts/check-date-label.php
 */

declare(strict_types=1);

$file = '/var/www/html/web/modules/custom/ksa_agenda/src/DateLabel.php';
if (!is_file($file)) {
  fwrite(STDERR, "RED: $file does not exist.\n");
  exit(1);
}
require $file;

use Drupal\ksa_agenda\DateLabel;

$e = static fn (string $start, string $end, ?string $from = NULL, ?string $to = NULL) => [
  'start' => $start, 'end' => $end, 'start_time' => $from, 'end_time' => $to,
];

$cases = [
  'één dag' => [$e('2026-10-18', '2026-10-18'), 'zo 18 oktober 2026'],
  'zelfde maand' => [$e('2027-01-29', '2027-01-31'), 'vr 29 januari 2027 – zo 31 januari 2027'],
  'over maandgrens' => [$e('2027-04-30', '2027-05-02'), 'vr 30 april 2027 – zo 2 mei 2027'],
  'over jaargrens' => [$e('2026-12-28', '2027-01-02'), 'ma 28 december 2026 – za 2 januari 2027'],
  'met uren' => [$e('2026-10-10', '2026-10-10', '14:00', '17:00'), 'za 10 oktober 2026, 14:00 – 17:00'],
  'enkel beginuur' => [$e('2026-10-10', '2026-10-10', '14:00', '14:00'), 'za 10 oktober 2026, 14:00'],
  'uren over meerdere dagen' => [$e('2026-10-10', '2026-10-11', '18:00', '12:00'), 'za 10 oktober 2026, 18:00 – zo 11 oktober 2026, 12:00'],
];

$pass = 0;
foreach ($cases as $label => [$event, $expected]) {
  $got = DateLabel::format($event);
  $ok = $got === $expected;
  $pass += (int) $ok;
  printf("%s %-26s %s\n", $ok ? 'OK  ' : 'FAIL', $label, $ok ? $got : "expected '$expected', got '$got'");
}
echo "\n$pass of " . count($cases) . " checks passed.\n";
exit($pass === count($cases) ? 0 : 1);
