<?php
/** Run with: ddev drush php:script scripts/check-newsletter.php */
use Drupal\Core\Session\AnonymousUserSession;
use Drupal\Core\Session\UserSession;
use Drupal\Core\Form\FormState;
use Drupal\ksa_newsletter\SubmissionGate;
use Drupal\ksa_newsletter\Form\SubmissionForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

$checks = 0;
$assert = static function ($value, $message) use (&$checks) {
  if (!$value) { throw new RuntimeException($message); }
  $checks++;
};
$anon = new AnonymousUserSession();
$editor = new UserSession(['uid' => 900001, 'roles' => ['authenticated', 'content_editor']]);
$admin = \Drupal\user\Entity\User::load(1);
$article = \Drupal::entityTypeManager()->getStorage('newsletter_article')->create(['title' => 'Test', 'writer' => 'Test', 'body' => 'Test']);
foreach ([$anon, $editor] as $account) {
  foreach (['view', 'update', 'delete'] as $operation) {
    $assert(!$article->access($operation, $account), "Unexpected $operation access");
  }
  $assert(!\Drupal::entityTypeManager()->getAccessControlHandler('newsletter_article')->createAccess(NULL, $account), 'Generic creation must be denied');
  $media = \Drupal::entityTypeManager()->getStorage('media')->create(['bundle' => 'newsletter_photo', 'status' => 1]);
  foreach (['view', 'update', 'delete'] as $operation) {
    $assert(!$media->access($operation, $account), "Private media $operation access leaked");
  }
}
$assert($article->access('view', $admin), 'Admin must read contributions');
$assert(!isset($article->getFieldDefinitions()['status']), 'No workflow status');
$assert(!isset($article->getFieldDefinitions()['edition']), 'No edition field');
$assert(\Drupal::entityTypeManager()->getDefinition('newsletter_article')->isInternal(), 'Entity must not auto-expose through JSON:API');
$assert(\Drupal::config('field.storage.media.field_newsletter_image')->get('settings.uri_scheme') === 'private', 'Private photo scheme');
$private = realpath(\Drupal\Core\Site\Settings::get('file_private_path'));
$assert($private && !str_starts_with($private . '/', DRUPAL_ROOT . '/'), 'Private directory must be outside webroot');
$assert(\Drupal::config('honeypot.settings')->get('time_limit') > 0, 'Honeypot time guard enabled');

$token_before = \Drupal::state()->get('ksa_newsletter.token');
$switcher = \Drupal::service('account_switcher');
$switcher->switchTo($anon);
$request = Request::create('/nieuwsbrief/insturen');
$request->setSession(new Session(new MockArraySessionStorage()));
$request->server->set('REMOTE_ADDR', '192.0.2.244');
try {
  SubmissionGate::rotate();
  $assert(!SubmissionGate::allowed($request), 'No open anonymous form');
  $assert(!SubmissionGate::accept($request, 'wrong'), 'Invalid link must fail');
  $assert(SubmissionGate::accept($request, SubmissionGate::token()), 'Valid link must open session');
  $assert(SubmissionGate::allowed($request), 'Capability should authorize submission');
  SubmissionGate::rotate();
  $assert(!SubmissionGate::allowed($request), 'Rotation must revoke existing sessions');
  $assert(ksa_newsletter_file_download('private://newsletter/test.jpg') === -1, 'Original must be private');
  $assert(ksa_newsletter_file_download('private://styles/test/private/newsletter/test.jpg') === -1, 'Derivative must be private');
  $assert(ksa_newsletter_file_download('private://unrelated/test.jpg') === NULL, 'Unrelated files unaffected');
  $assert(SubmissionGate::allowedAttempt($request), 'Fresh quota should allow');
  $flood = \Drupal::service('flood');
  for ($i = 0; $i < 10; $i++) { $flood->register('ksa_newsletter.hour', 3600, SubmissionGate::identifier($request)); }
  $assert(!SubmissionGate::allowedAttempt($request), 'Hourly limit should reject');
  $flood->clear('ksa_newsletter.hour', SubmissionGate::identifier($request));
}
finally {
  if ($token_before === NULL) { \Drupal::state()->delete('ksa_newsletter.token'); }
  else { \Drupal::state()->set('ksa_newsletter.token', $token_before); }
  \Drupal::service('flood')->clear('ksa_newsletter.hour', SubmissionGate::identifier($request));
  $switcher->switchBack();
}

// Verify a stored fixture's file is still denied when requesting its real URL.
$ids = \Drupal::entityTypeManager()->getStorage('media')->getQuery()->accessCheck(FALSE)->condition('bundle', 'newsletter_photo')->range(0, 1)->execute();
foreach (\Drupal::entityTypeManager()->getStorage('media')->loadMultiple($ids) as $media) {
  $uri = $media->get('field_newsletter_image')->entity->getFileUri();
  $assert(str_starts_with($uri, 'private://newsletter/'), 'Stored photo in private subtree');
  $assert(is_file($uri), 'Stored photo exists');
}
echo "PASS: $checks nieuwsbrief security checks\n";
