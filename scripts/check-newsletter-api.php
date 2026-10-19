<?php
/** Isolated API checks: fake credential and articles restored afterwards. */
use Drupal\ksa_newsletter\Controller\ApiController;
use Drupal\Core\Session\AnonymousUserSession;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

$state = \Drupal::state();
$old = $state->get('ksa_newsletter.api_hash');
$transaction = \Drupal::database()->startTransaction();
$switcher = \Drupal::service('account_switcher');
$switcher->switchTo(new AnonymousUserSession());
$key = 'ksa_nl_' . bin2hex(random_bytes(32));
$state->set('ksa_newsletter.api_hash', hash('sha256', $key));
$count = 0;
$assert = function ($condition, $label) use (&$count) { if (!$condition) { throw new RuntimeException($label); } $count++; };
$controller = new ApiController();
$request = function (array $query = [], ?string $token = NULL) {
  $r = Request::create('https://ksa.ddev.site/api/nieuwsbrief/artikelen', 'GET', $query);
  $r->server->set('REMOTE_ADDR', '192.0.2.241');
  if ($token !== NULL) { $r->headers->set('Authorization', 'Bearer ' . $token); }
  return $r;
};
try {
  $assert($controller->articles(Request::create('http://ksa.ddev.site/api/nieuwsbrief/artikelen'))->getStatusCode() === 400, 'Require HTTPS');
  $assert($controller->articles($request())->getStatusCode() === 401, 'Missing key');
  $assert($controller->articles($request(['token' => $key]))->getStatusCode() === 401, 'Query token must not authorize');
  $assert($controller->articles($request([], 'ksa_nl_' . str_repeat('0', 64)))->getStatusCode() === 401, 'Wrong key');
  foreach ([['van' => '2026-02-30'], ['van' => ['bad']], ['van' => '2026-02-02', 'tot' => '2026-02-01'], ['limit' => '101'], ['limit' => '0'], ['after' => '-1'], ['limit' => ['bad']]] as $query) {
    $assert($controller->articles($request($query, $key))->getStatusCode() === 400, 'Invalid parameters');
  }
  $storage = \Drupal::entityTypeManager()->getStorage('newsletter_article');
  $existing = $storage->loadMultiple($storage->getQuery()->accessCheck(FALSE)->execute());
  $photos = [];
  foreach ($existing as $item) { if (!$item->get('photos')->isEmpty()) { $photos = $item->get('photos')->getValue(); break; } }
  $last_id = (int) (max(array_keys($existing) ?: [0]));
  $ids = [];
  foreach (['2026-10-01 00:00:00', '2026-10-31 23:59:59', '2026-11-01 00:00:00'] as $date) {
    $article = $storage->create(['title' => 'API TEST', 'writer' => 'Fictief', 'body' => '<h2>Kop</h2><p><strong>Vet</strong> en <em>cursief</em></p>', 'created' => (new DateTimeImmutable($date, new DateTimeZone('Europe/Brussels')))->getTimestamp(), 'photos' => $photos]);
    $article->save(); $ids[] = $article->id();
  }
  $query = ['van' => '2026-10-01', 'tot' => '2026-10-31', 'after' => (string) $last_id, 'limit' => '1'];
  $response = $controller->articles($request($query, $key));
  $assert($response->getStatusCode() === 200, 'Valid bearer');
  $data = json_decode($response->getContent(), TRUE);
  $assert(count($data['data']) === 1 && $data['pagination']['next'], 'Pagination');
  $assert(str_contains($data['data'][0]['text_html'], '<strong>Vet</strong>'), 'Rich text retained');
  parse_str(parse_url($data['pagination']['next'], PHP_URL_QUERY), $next);
  $data2 = json_decode($controller->articles($request($next, $key))->getContent(), TRUE);
  $assert(count($data2['data']) === 1 && $data2['pagination']['next'] === NULL, 'Inclusive end day / no November data');
  $assert($data['data'][0]['id'] !== $data2['data'][0]['id'], 'No duplicate pages');
  if ($photos) {
    $photo = $data['data'][0]['photos'][0];
    $assert(str_contains($photo['url'], '/api/nieuwsbrief/artikelen/') && $photo['url'] === $photo['download_url'], 'Photo URL present');
    $assert($photo['requires_authorization'] && !str_contains($photo['url'], $key), 'No key in photo URL');
    $assert($controller->photo($request(), $data['data'][0]['id'], $photo['id'])->getStatusCode() === 401, 'Anonymous photo denied');
    $download = $controller->photo($request([], $key), $data['data'][0]['id'], $photo['id']);
    $assert($download instanceof BinaryFileResponse && $download->getFile()->isFile(), 'Authorized photo bytes available');
    $assert($download->headers->get('X-Content-Type-Options') === 'nosniff', 'Photo security headers');
    $assert($controller->photo($request([], $key), $data['data'][0]['id'], \Drupal::service('uuid')->generate())->getStatusCode() === 404, 'Unrelated photo denied');
  }
  $state->set('ksa_newsletter.api_hash', hash('sha256', 'revoked'));
  $assert($controller->articles($request([], $key))->getStatusCode() === 401, 'Revocation');
  $r = $request([], $key);
  $flood = \Drupal::service('flood');
  for ($i = 0; $i < 120; $i++) { $flood->register('ksa_newsletter.api', 60, \Drupal\ksa_newsletter\SubmissionGate::identifier($r)); }
  $assert($controller->articles($r)->getStatusCode() === 429, 'Rate limit');
  echo "PASS: $count newsletter API checks; all test records rolled back.\n";
}
finally {
  $transaction->rollBack();
  $state->resetCache();
  if ($old === NULL) { $state->delete('ksa_newsletter.api_hash'); } else { $state->set('ksa_newsletter.api_hash', $old); }
  $switcher->switchBack();
}
