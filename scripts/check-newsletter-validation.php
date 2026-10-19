<?php
/** Isolated validation tests; the transaction rolls back flood test events. */
use Drupal\Core\Form\FormState;
use Drupal\Core\Session\AnonymousUserSession;
use Drupal\ksa_newsletter\Form\SubmissionForm;
use Drupal\ksa_newsletter\SubmissionGate;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

$transaction = \Drupal::database()->startTransaction();
$original_token = \Drupal::state()->get('ksa_newsletter.token');
$switcher = \Drupal::service('account_switcher');
$switcher->switchTo(new AnonymousUserSession());
$stack = \Drupal::requestStack();
$tmp = tempnam(sys_get_temp_dir(), 'newsletter-validation-');
file_put_contents($tmp, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/l9sAAAAASUVORK5CYII='));
$checks = 0;
try {
  $request = Request::create('/nieuwsbrief/insturen', 'POST');
  $request->server->set('REMOTE_ADDR', '192.0.2.243');
  $request->setSession(new Session(new MockArraySessionStorage()));
  if (SubmissionGate::token() === '') { SubmissionGate::rotate(); }
  SubmissionGate::accept($request, SubmissionGate::token());
  $stack->push($request);
  $form = new SubmissionForm();
  $uuid = \Drupal::service('uuid')->generate();
  $base = ['submission_id' => $uuid, 'submission_token' => \Drupal::csrfToken()->get('ksa_newsletter:' . $uuid), 'title' => 'Fictieve test', 'writer' => 'Test', 'body' => 'Testtekst', 'activity_date' => ''];
  $validate = function (array $values, array $uploads, bool $expected) use ($form, $request, &$checks) {
    $request->files->replace(['files' => ['photos' => $uploads]]);
    $state = new FormState();
    $state->clearErrors();
    $state->setValues($values);
    $build = [];
    $form->validateForm($build, $state);
    if ((!$state->hasAnyErrors()) !== $expected) { throw new RuntimeException('Validation expectation failed: ' . json_encode($state->getErrors())); }
    $checks++;
    $state->clearErrors();
  };
  $good = new UploadedFile($tmp, 'test.png', 'image/png', NULL, TRUE);
  $bad_extension = new UploadedFile($tmp, 'test.php', 'image/png', NULL, TRUE);
  $validate($base, [], TRUE);
  $validate($base, [$good], TRUE);
  $validate(array_replace($base, ['body' => '  ']), [], FALSE);
  $validate(array_replace($base, ['body' => str_repeat('a', 20001)]), [], FALSE);
  $validate(array_replace($base, ['submission_token' => 'forged']), [], FALSE);
  $validate(array_replace($base, ['submission_id' => \Drupal::service('uuid')->generate()]), [], FALSE);
  $validate($base, array_fill(0, 6, $good), FALSE);
  $validate($base, [$bad_extension], FALSE);
  file_put_contents($tmp, '<script>alert(1)</script>');
  $validate($base, [new UploadedFile($tmp, 'fake.jpg', 'image/jpeg', NULL, TRUE)], FALSE);
  $validate(array_replace($base, ['body' => '<p><strong>Vet</strong> en <em>cursief</em></p>', 'body_is_html' => '1']), [], TRUE);
  $validate(array_replace($base, ['body' => '<p><strong>&nbsp;</strong></p>', 'body_is_html' => '1']), [], FALSE);
  $safe = \Drupal\ksa_newsletter\NewsletterText::clean('<p class="word" onclick="alert(1)"><b>Vet</b> <i>Cursief</i><img src="x" onerror="alert(1)"><a href="javascript:alert(1)">link</a></p>', TRUE);
  if ($safe !== '<p><strong>Vet</strong> <em>Cursief</em>link</p>') { throw new RuntimeException('Unsafe or unexpected HTML: ' . $safe); }
  $checks++;
  $plain = \Drupal\ksa_newsletter\NewsletterText::clean('<strong>literal</strong>', FALSE);
  if ($plain !== '&lt;strong&gt;literal&lt;/strong&gt;') { throw new RuntimeException('Plain text fallback changed.'); }
  $checks++;
  $rich = '<h2>Tussenkop</h2><ul><li>Een</li><li>Twee</li></ul><ol><li>Stap</li></ol><p><a href="https://example.com/inschrijven?a=1&amp;b=2">Inschrijven</a></p>';
  if (\Drupal\ksa_newsletter\NewsletterText::clean($rich, TRUE) !== $rich) { throw new RuntimeException('Headings, lists or links were lost.'); }
  $checks++;
  foreach (['javascript:alert(1)', 'jav&#x61;script:alert(1)', 'data:text/html,test', 'vbscript:test', '//example.com', 'java&#10;script:alert(1)'] as $href) {
    $safe = \Drupal\ksa_newsletter\NewsletterText::clean('<a href="' . $href . '" style="color:red" target="_blank">Link</a>', TRUE);
    if ($safe !== 'Link') { throw new RuntimeException('Unsafe URL survived: ' . $safe); }
    $checks++;
  }
  foreach (['mailto:test@example.com', '/contact', '#praktisch'] as $href) {
    if (!str_contains(\Drupal\ksa_newsletter\NewsletterText::clean('<a href="' . $href . '" onclick="bad()">Link</a>', TRUE), 'href="' . $href . '"')) { throw new RuntimeException('Safe URL lost.'); }
    $checks++;
  }
  $request->files->replace([]);
  $stack->pop();
  echo "PASS: $checks newsletter input/upload validation checks\n";
}
finally {
  @unlink($tmp);
  $transaction->rollBack();
  \Drupal::state()->resetCache();
  $switcher->switchBack();
}
