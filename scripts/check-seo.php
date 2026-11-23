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
$paths = ['/', '/?page=1', '/nieuws', '/nieuws?page=1', '/nieuws?page=99', '/nieuws?page=-1', '/nieuws?page=abc', '/seo-pagina-bestaat-niet', '/node/51', '/sitemap.xml', '/home', '/node/39', '/ploegen/16', '/node', '/ploegen', '/blog?page=3', '/nieuws?page=0', '/rss.xml'];
$leaders = [];
foreach (\Drupal::entityTypeManager()->getStorage('node')->loadMultiple() as $node) {
  if ($node->isPublished()) {
    $paths[] = $node->toUrl()->toString();
    if ($node->bundle() === 'leader') {
      $leaders[] = $node->toUrl()->toString();
    }
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
      // Leader pages left the sitemap on 2026-11-16: about 30 URLs remain.
      $checks['sitemap generated'] = $results[$path]['status'] === 200 && count($urls) > 25;
      $checks['home alias not in sitemap'] = !in_array('https://ksa.ddev.site/home', $urls);
      $checks['leader pages not in sitemap'] = !array_filter($urls, static fn($url) => str_starts_with($url, 'https://ksa.ddev.site/leiding/'));
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
    if ($result['status'] === 200 && !in_array($path, ['/sitemap.xml', '/node/51', '/rss.xml'], TRUE)) {
      $checks[$path . ' one H1'] = $result['h1_count'] === 1;
      $checks[$path . ' description'] = $result['description'] !== '';
      $checks[$path . ' canonical'] = $result['canonical'] !== '';
    }
  }
  foreach (\Drupal::entityTypeManager()->getStorage('redirect')->loadMultiple() as $redirect) {
    $path = '/' . $redirect->get('redirect_source')->path;
    $checks[$path . ' permanent redirect'] = $results[$path]['status'] === 301 && $results[$path]['location'];
    // No chains and no dead ends: the target answers 200 right away. Only a
    // target on this site can be checked here. A file is looked up on disk,
    // without its query string and with %20 and the like decoded.
    $location = (string) $results[$path]['location'];
    $host = parse_url($location, PHP_URL_HOST);
    if ($host === NULL || $host === 'ksa.ddev.site') {
      $target = preg_replace('#^https?://[^/]+#', '', $location);
      $file = rawurldecode((string) parse_url($target, PHP_URL_PATH));
      $checks[$path . ' target exists'] = str_starts_with($file, '/sites/default/files/')
        ? file_exists(DRUPAL_ROOT . $file)
        : ($results[$target]['status'] ?? 0) === 200;
    }
  }

  // Go-live audit of 2026-11-16.
  $checks['/node listing is gone'] = $results['/node']['status'] === 404;
  $checks['/ploegen redirects to /leiding'] = $results['/ploegen']['status'] === 301 && str_ends_with((string) $results['/ploegen']['location'], '/leiding');
  // The redirect keeps the visitor's query string, so the target sets page=0
  // itself: otherwise /blog?page=3 would end on the missing /nieuws?page=3.
  $checks['old blog pager lands on the archive'] = $results['/blog?page=3']['status'] === 301 && str_ends_with((string) $results['/blog?page=3']['location'], '/nieuws?page=0');
  foreach ($leaders as $path) {
    $checks[$path . ' noindex'] = str_contains($results[$path]['robots'], 'noindex');
  }
  $suffix = ' | KSA Deinze-Astene';
  foreach ([
    '/leiding' => 'Leiding en leeftijdsgroepen',
    '/rondes' => 'Rondes, activiteiten en kampen',
    '/ons-verhaal' => 'Over onze jeugdbeweging',
    '/nieuws' => 'Nieuws',
    '/ploegen/knim' => 'Knim: 1ste en 2de middelbaar',
  ] as $path => $title) {
    $checks[$path . ' search title'] = $results[$path]['title'] === $title . $suffix;
  }
  // What the search title promises is also on the page itself.
  $checks['/ploegen/knim shows place and age'] = $results['/ploegen/knim']['team_meta'] === 'Deinze · 1ste en 2de middelbaar';
  $checks['/ploegen/knim shows its description'] = mb_strlen($results['/ploegen/knim']['team_text']) > 40;
  foreach (['/nieuws/startdag-2026', '/ploegen/knim'] as $path) {
    $checks[$path . ' light share image'] = str_contains($results[$path]['og_image'], '/styles/social_share/');
  }
  $checks['/nieuws share tags'] = $results['/nieuws']['og_title'] !== '' && $results['/nieuws']['og_image'] !== '';
  $checks['robots.txt names the sitemap'] = str_contains((string) file_get_contents(DRUPAL_ROOT . '/robots.txt'), "\nSitemap: ");
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
