<?php

/**
 * Read-only anonymous rendering audit. Run with ddev drush php:script.
 * Optional JSON report: scripts/check-seo.php writes docs/qa/seo-after-2026-10-07.json.
 */

use Drupal\Core\Session\AnonymousUserSession;

$switcher = \Drupal::service('account_switcher');
$switcher->switchTo(new AnonymousUserSession());
$checks = [];
$results = [];
$paths = ['/', '/?page=1', '/nieuws', '/nieuws?page=1', '/nieuws?page=99', '/nieuws?page=-1', '/nieuws?page=abc', '/seo-pagina-bestaat-niet', '/node/51', '/sitemap.xml', '/home', '/node/39', '/ploegen/16'];
foreach (\Drupal::entityTypeManager()->getStorage('node')->loadMultiple() as $node) {
  if ($node->isPublished()) {
    $paths[] = $node->toUrl()->toString();
  }
}
foreach (\Drupal::entityTypeManager()->getStorage('redirect')->loadMultiple() as $redirect) {
  $paths[] = '/' . $redirect->get('redirect_source')->path;
}
try {
  foreach (array_unique($paths) as $path) {
    $process = proc_open([PHP_BINARY, DRUPAL_ROOT . '/../scripts/seo-response.php', $path], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    $json = stream_get_contents($pipes[1]);
    $errors = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    if (proc_close($process) !== 0) {
      throw new RuntimeException($path . ': ' . $errors);
    }
    $results[$path] = json_decode($json, TRUE, 512, JSON_THROW_ON_ERROR);
    if ($path === '/sitemap.xml') {
      $urls = $results[$path]['urls'];
      $checks['sitemap generated'] = $results[$path]['status'] === 200 && count($urls) > 40;
      $checks['404 excluded from sitemap'] = !in_array('https://ksa.ddev.site/node/51', $urls);
      $checks['hidden group excluded from sitemap'] = !in_array('https://ksa.ddev.site/ploegen/16', $urls);
      $checks['news archive in sitemap'] = in_array('https://ksa.ddev.site/nieuws', $urls);
    }
  }
  foreach (['/nieuws?page=99', '/nieuws?page=-1', '/nieuws?page=abc', '/seo-pagina-bestaat-niet'] as $path) {
    $checks[$path . ' returns 404'] = $results[$path]['status'] === 404;
    $checks[$path . ' noindex'] = str_contains($results[$path]['robots'], 'noindex');
  }
  $checks['direct error node noindex'] = str_contains($results['/node/51']['robots'], 'noindex');
  $checks['archive second page canonical'] = $results['/nieuws?page=1']['canonical'] === 'https://ksa.ddev.site/nieuws?page=1';
  $checks['archive first page canonical'] = $results['/nieuws']['canonical'] === 'https://ksa.ddev.site/nieuws';
  $checks['home ignores unused pager'] = $results['/?page=1']['status'] === 200 && $results['/?page=1']['canonical'] === 'https://ksa.ddev.site/';
  $checks['archive page titles differ'] = $results['/nieuws']['title'] !== $results['/nieuws?page=1']['title'];
  foreach (['/home', '/node/39'] as $path) {
    $checks[$path . ' redirects to home'] = $results[$path]['status'] === 301 && $results[$path]['location'] === 'https://ksa.ddev.site/';
  }
  $checks['hidden group unavailable'] = in_array($results['/ploegen/16']['status'], [403, 404], TRUE);
  $checks['home schema'] = !empty($results['/']['json_ld']);
  foreach ($results as $path => $result) {
    if ($result['status'] === 200 && $path !== '/sitemap.xml' && $path !== '/node/51') {
      $checks[$path . ' one H1'] = $result['h1_count'] === 1;
      $checks[$path . ' description'] = $result['description'] !== '';
      $checks[$path . ' canonical'] = $result['canonical'] !== '';
    }
  }
  foreach (\Drupal::entityTypeManager()->getStorage('redirect')->loadMultiple() as $redirect) {
    $path = '/' . $redirect->get('redirect_source')->path;
    $checks[$path . ' permanent redirect'] = $results[$path]['status'] === 301 && $results[$path]['location'];
  }
}
finally {
  $switcher->switchBack();
}
$failed = array_keys(array_filter($checks, static fn($passed) => !$passed));
file_put_contents(DRUPAL_ROOT . '/../docs/qa/seo-after-2026-10-07.json', json_encode(['checks' => $checks, 'results' => $results], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
echo count($checks) . ' checks, ' . count($failed) . ' failed.' . PHP_EOL;
foreach ($failed as $name) {
  echo 'FAIL: ' . $name . PHP_EOL;
}
if ($failed) {
  throw new RuntimeException('SEO audit failed.');
}
