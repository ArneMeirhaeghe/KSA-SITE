<?php
namespace Drupal\ksa_newsletter\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\File\FileExists;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\Upload\FormUploadedFile;
use Drupal\ksa_newsletter\SubmissionGate;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/** No asynchronous upload endpoint: all guards run before files are stored. */
final class SubmissionForm extends FormBase {
  public function getFormId() { return 'ksa_newsletter_submission'; }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    if (!SubmissionGate::allowed($this->getRequest())) {
      throw new AccessDeniedHttpException();
    }
    $form['#cache']['max-age'] = 0;
    // Core only adds form tokens for authenticated accounts. This public form
    // already has a session, so also require a session-bound token for guests.
    $uuid = (string) $this->getRequest()->request->get('submission_id', '');
    if (!\Drupal\Component\Uuid\Uuid::isValid($uuid)) { $uuid = \Drupal::service('uuid')->generate(); }
    $form['submission_id'] = ['#type' => 'hidden', '#default_value' => $uuid];
    $form['submission_token'] = ['#type' => 'hidden', '#default_value' => \Drupal::csrfToken()->get('ksa_newsletter:' . $uuid)];
    $form['#attributes']['class'][] = 'ksa-newsletter-form';
    $form['#attributes']['enctype'] = 'multipart/form-data';
    $form['#attached']['library'][] = 'ksa_newsletter/form';
    $form['#attached']['library'][] = 'ksa_newsletter/editor';
    $form['title'] = ['#type' => 'textfield', '#title' => 'Titel', '#required' => TRUE, '#maxlength' => 200];
    $form['writer'] = ['#type' => 'textfield', '#title' => 'Jouw naam', '#required' => TRUE, '#maxlength' => 120, '#autocomplete_route_name' => NULL];
    $form['body'] = ['#type' => 'textarea', '#title' => 'Je tekst', '#required' => TRUE, '#rows' => 10, '#description' => 'Schrijf in je eigen woorden. Gebruik de werkbalk voor tussenkoppen, opmaak, lijsten en links. Maximaal 20.000 tekens.', '#attributes' => ['data-newsletter-editor' => 'true']];
    $form['body_is_html'] = ['#type' => 'hidden', '#default_value' => '0'];
    $form['activity_date'] = ['#type' => 'date', '#title' => 'Datum van de activiteit', '#description' => 'Optioneel. De datum waarop je instuurt, bewaren we automatisch.'];
    $form['photos'] = ['#type' => 'file', '#title' => 'Foto’s', '#multiple' => TRUE, '#attributes' => ['accept' => 'image/jpeg,image/png,image/webp'], '#description' => 'Optioneel: maximaal 5 JPG-, PNG- of WebP-foto’s. Maximaal 5 MB per foto en 20 MB samen. Gebruik alleen foto’s die je voor de nieuwsbrief mag delen.'];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = ['#type' => 'submit', '#value' => 'Bijdrage insturen'];
    $form['privacy'] = ['#type' => 'html_tag', '#tag' => 'p', '#value' => 'Alleen de nieuwsbriefbeheerder kan je bijdrage bekijken. Ze verschijnt niet automatisch op de website.', '#attributes' => ['class' => ['newsletter-note']]];
    \Drupal::service('honeypot')->addFormProtection($form, $form_state, ['honeypot', 'time_restriction']);
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $request = $this->getRequest();
    if (!SubmissionGate::allowed($request)) { throw new AccessDeniedHttpException(); }
    if (!\Drupal::csrfToken()->validate((string) $form_state->getValue('submission_token'), 'ksa_newsletter:' . $form_state->getValue('submission_id'))) {
      $form_state->setErrorByName('title', 'Je formulier is verlopen. Herlaad de pagina en probeer opnieuw.');
      return;
    }
    // Serialize the quota check/register pair, including concurrent attempts.
    $lock = \Drupal::lock();
    if (!$lock->acquire('ksa_newsletter.quota', 10)) {
      $form_state->setErrorByName('title', 'Het is even druk. Probeer over een ogenblik opnieuw.');
      return;
    }
    try {
      if (!SubmissionGate::allowedAttempt($request)) {
        $form_state->setErrorByName('title', 'Er zijn te veel instuurpogingen. Probeer later opnieuw of neem contact op met de nieuwsbriefbeheerder.');
        return;
      }
      SubmissionGate::registerAttempt($request);
    }
    finally { $lock->release('ksa_newsletter.quota'); }
    foreach (['title' => 200, 'writer' => 120] as $key => $limit) {
      $value = trim((string) $form_state->getValue($key));
      $form_state->setValue($key, $value);
      if ($value === '' || mb_strlen($value) > $limit) {
        $form_state->setErrorByName($key, $this->t('Vul dit veld in met maximaal @limit tekens.', ['@limit' => $limit]));
      }
    }
    $raw_body = (string) $form_state->getValue('body');
    $html = \Drupal\ksa_newsletter\NewsletterText::clean($raw_body, $form_state->getValue('body_is_html') === '1');
    $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if (preg_match('/^[\s\x{00a0}\x{200b}]*$/u', $text) || mb_strlen($text) > 20000 || strlen($raw_body) > 200000) {
      $form_state->setErrorByName('body', 'Vul je tekst in met maximaal 20.000 tekens.');
    }
    $form_state->set('newsletter_body_html', $html);
    $uploads = $request->files->all('files')['photos'] ?? [];
    if (!is_array($uploads) || count($uploads) > 5) {
      $form_state->setErrorByName('photos', 'Kies maximaal vijf foto’s.');
      return;
    }
    $total = 0;
    foreach ($uploads as $upload) {
      if (!$upload || !$upload->isValid()) {
        $form_state->setErrorByName('photos', 'Een foto kon niet worden geüpload. Kies de bestanden opnieuw.');
        continue;
      }
      $size = $upload->getSize();
      $total += $size;
      $info = @getimagesize($upload->getPathname());
      $extension = strtolower($upload->getClientOriginalExtension());
      if ($size > 5 * 1024 * 1024 || !in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], TRUE) || !$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], TRUE) || $info[0] * $info[1] > 25000000 || $info[0] > 10000 || $info[1] > 10000) {
        $form_state->setErrorByName('photos', 'Gebruik echte JPG-, PNG- of WebP-foto’s van maximaal 5 MB en 25 megapixels (maximaal 10.000 pixels per zijde).');
      }
    }
    if ($total > 20 * 1024 * 1024) {
      $form_state->setErrorByName('photos', 'De foto’s mogen samen maximaal 20 MB groot zijn.');
    }
    if ($form_state->hasAnyErrors() && $uploads) {
      $this->messenger()->addWarning('Kies je foto’s opnieuw wanneer je de fout hebt verbeterd.');
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    if (!SubmissionGate::allowed($this->getRequest())) { throw new AccessDeniedHttpException(); }
    $storage = \Drupal::entityTypeManager()->getStorage('newsletter_article');
    $uuid = $form_state->getValue('submission_id');
    $lock = \Drupal::lock();
    if (!$lock->acquire('ksa_newsletter.submit.' . $uuid, 60)) {
      $this->messenger()->addWarning('Deze bijdrage wordt al verwerkt.');
      $form_state->setRebuild();
      return;
    }
    $files = [];
    $media_items = [];
    try {
      // The form UUID makes a retry of the same POST idempotent.
      if ($storage->getQuery()->accessCheck(FALSE)->condition('uuid', $uuid)->count()->execute()) {
        $this->getRequest()->getSession()->getFlashBag()->add('ksa_newsletter.received', TRUE);
        $form_state->setRedirect('ksa_newsletter.entry');
        return;
      }
      $uploads = $this->getRequest()->files->all('files')['photos'] ?? [];
      $directory = 'private://newsletter/' . $uuid;
      if ($uploads && !\Drupal::service('file_system')->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS)) {
        throw new \RuntimeException('Private upload directory unavailable.');
      }
      foreach ($uploads as $index => $upload) {
        $result = \Drupal::service('file.upload_handler')->handleFileUpload(new FormUploadedFile($upload), ['FileExtension' => ['extensions' => 'jpg jpeg png webp'], 'FileSizeLimit' => ['fileLimit' => 5 * 1024 * 1024], 'FileIsImage' => []], $directory, FileExists::Rename);
        if ($result->hasViolations()) { throw new \RuntimeException('Image validation failed.'); }
        $file = $result->getFile();
        $files[] = $file;
        $file->setPermanent();
        $file->save();
        $media = \Drupal::entityTypeManager()->getStorage('media')->create([
          'bundle' => 'newsletter_photo', 'name' => $form_state->getValue('title') . ' — foto ' . ($index + 1),
          'uid' => 0, 'status' => 0,
          'field_newsletter_image' => ['target_id' => $file->id(), 'alt' => 'Foto bij ' . $form_state->getValue('title')],
        ]);
        $media->save();
        $media_items[] = $media;
      }
      $article = $storage->create([
        'uuid' => $uuid, 'title' => $form_state->getValue('title'), 'writer' => $form_state->getValue('writer'),
        'body' => $form_state->get('newsletter_body_html'), 'activity_date' => $form_state->getValue('activity_date') ?: NULL,
        'uid' => $this->currentUser()->id(), 'photos' => array_map(fn($media) => ['target_id' => $media->id()], $media_items),
      ]);
      $article->save();
      $this->getRequest()->getSession()->getFlashBag()->add('ksa_newsletter.received', TRUE);
      $form_state->setRedirect('ksa_newsletter.entry');
    }
    catch (\Throwable $exception) {
      foreach (array_reverse($media_items) as $media) { $media->delete(); }
      foreach ($files as $file) { $file->delete(); }
      \Drupal::logger('ksa_newsletter')->error('Opslaan nieuwsbriefbijdrage mislukt: @type', ['@type' => get_class($exception)]);
      $this->messenger()->addError('Je bijdrage is niet opgeslagen. Je tekst blijft hieronder staan. Kies de foto’s opnieuw en probeer nogmaals.');
      $form_state->setRebuild();
    }
    finally { $lock->release('ksa_newsletter.submit.' . $uuid); }
  }
}
