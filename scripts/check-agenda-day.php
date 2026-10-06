<?php
/** Run: ddev exec php scripts/check-agenda-day.php. No site data is changed. */
declare(strict_types=1);
$loader = require dirname(__DIR__) . '/vendor/autoload.php';
$loader->addPsr4('Drupal\\Core\\', dirname(__DIR__) . '/web/core/lib/Drupal/Core');
$loader->addPsr4('Drupal\\Component\\', dirname(__DIR__) . '/web/core/lib/Drupal/Component');
$loader->addPsr4('Drupal\\ksa_agenda\\', dirname(__DIR__) . '/web/modules/custom/ksa_agenda/src');
use Drupal\Core\State\StateInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\ksa_agenda\StackMiddleware\AgendaDay;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
$state = new class implements StateInterface {
  public array $data = [];
  public function get($key, $default = NULL) { return $this->data[$key] ?? $default; }
  public function set($key, $value) { $this->data[$key] = $value; }
  public function getMultiple(array $keys) { return array_intersect_key($this->data, array_flip($keys)); }
  public function setMultiple(array $data) { $this->data = $data + $this->data; }
  public function delete($key) { unset($this->data[$key]); }
  public function deleteMultiple(array $keys) { foreach ($keys as $key) $this->delete($key); }
  public function resetCache() {}
  public function getValuesSetDuringRequest(string $key): ?array { return NULL; }
};
$time = new class implements TimeInterface {
  public int $now;
  public function getRequestTime() { return $this->now; }
  public function getCurrentTime() { return $this->now; }
  public function getRequestMicroTime() { return (float) $this->now; }
  public function getCurrentMicroTime() { return (float) $this->now; }
};
$cache = new class implements CacheTagsInvalidatorInterface, HttpKernelInterface {
  public int $invalidations = 0;
  public array $tags = [];
  public ?Response $cached = NULL;
  public function invalidateTags(array $tags) { $this->invalidations++; $this->tags = $tags; $this->cached = NULL; }
  public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = TRUE): Response {
    return $this->cached ??= new Response('Rendered after invalidation ' . $this->invalidations);
  }
};
$middleware = new AgendaDay($cache, $state, $cache, $time);
$request = Request::create('/rondes');
$checks = [];
$time->now = strtotime('2026-10-05 21:59:59 UTC');
$first = $middleware->handle($request);
$checks['first request renders'] = $first->getContent() === 'Rendered after invalidation 1';
$checks['same Brussels day hits page cache'] = $middleware->handle($request) === $first && $cache->invalidations === 1;
$time->now = strtotime('2026-10-05 22:00:00 UTC');
$second = $middleware->handle($request);
$checks['Brussels midnight expires full page before cache hit'] = $second !== $first && $second->getContent() === 'Rendered after invalidation 2';
$checks['only agenda tagged pages invalidated'] = $cache->tags === ['ksa_agenda'];
$time->now = strtotime('2026-10-06 22:00:00 UTC');
$middleware->handle($request, HttpKernelInterface::SUB_REQUEST);
$checks['subrequests do not advance the day'] = $cache->invalidations === 2;
$middleware->handle($request);
$checks['no successful feed fetch needed'] = $cache->invalidations === 3;
$time->now = strtotime('2026-11-10 22:59:59 UTC');
$middleware->handle($request);
$checks['winter midnight uses Brussels timezone'] = $state->get('ksa_agenda.render_day') === '2026-11-10';
$time->now++;
$middleware->handle($request);
$checks['winter midnight advances date'] = $state->get('ksa_agenda.render_day') === '2026-11-11';
foreach ($checks as $name => $ok) echo ($ok ? 'OK   ' : 'FAIL ') . $name . "\n";
exit(in_array(FALSE, $checks, TRUE) ? 1 : 0);
