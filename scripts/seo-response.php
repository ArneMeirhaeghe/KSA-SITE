<?php

// One real kernel per path: Drupal services cache route-specific state.
use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

$autoload = require __DIR__ . '/../vendor/autoload.php';
chdir(__DIR__ . '/../web');
$request = Request::create('https://ksa.ddev.site' . ($argv[1] ?? '/'), 'GET', [], [], [], [
  'SCRIPT_NAME' => '/index.php',
  'PHP_SELF' => '/index.php',
  'SCRIPT_FILENAME' => getcwd() . '/index.php',
]);
$kernel = DrupalKernel::createFromRequest($request, $autoload, 'prod');
$response = $kernel->handle($request);
$html = $response->getContent();
$doc = new DOMDocument();
@$doc->loadHTML('<?xml encoding="UTF-8">' . $html);
$xpath = new DOMXPath($doc);
$get = static fn($query) => $xpath->evaluate('string(' . $query . ')');
$result = [
  'status' => $response->getStatusCode(),
  'location' => $response->headers->get('Location'),
  'title' => $get('//title'),
  'h1_count' => $xpath->query('//h1')->length,
  'description' => $get('//meta[@name="description"]/@content'),
  'canonical' => $get('//link[@rel="canonical"]/@href'),
  'robots' => $get('//meta[@name="robots"]/@content'),
  'json_ld' => array_map(static fn($el) => json_decode($el->textContent, TRUE, 512, JSON_THROW_ON_ERROR), iterator_to_array($xpath->query('//script[@type="application/ld+json"]'))),
];
if (($argv[1] ?? '') === '/sitemap.xml') {
  $xml = simplexml_load_string($html);
  $result['urls'] = $xml ? array_map('strval', $xml->xpath('//*[local-name()="loc"]')) : [];
}
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
