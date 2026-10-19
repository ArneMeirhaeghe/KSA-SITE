<?php
namespace Drupal\ksa_newsletter\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\ksa_newsletter\Entity\NewsletterArticle;
use Drupal\ksa_newsletter\PublicPhotos;

final class PublishPhotosForm extends FormBase {
  public function getFormId() { return 'ksa_newsletter_publish_photos'; }
  public function buildForm(array $form, FormStateInterface $form_state, ?NewsletterArticle $newsletter_article = NULL): array {
    $form['#cache']['max-age'] = 0;
    $form_state->set('article_id', $newsletter_article->id());
    $form['intro'] = ['#markup' => '<p>Selecteer alleen de foto’s die in de e-mailnieuwsbrief mogen verschijnen. Drupal maakt openbare kopieën van maximaal 1600 × 1600 pixels, zonder uitsnijden. Iedereen met zo’n link kan de kopie bekijken en doorsturen. De originelen blijven privé.</p>'];
    $options = [];
    $form['previews'] = ['#type' => 'container', '#attributes' => ['class' => ['newsletter-photos']]];
    $form['#attached']['library'][] = 'ksa_newsletter/form';
    foreach ($newsletter_article->get('photos')->referencedEntities() as $media) {
      if ($media->bundle() !== 'newsletter_photo' || !$file = $media->get('field_newsletter_image')->entity) { continue; }
      $public = PublicPhotos::get($media);
      $options[$media->id()] = $file->getFilename() . ($public ? ' — al gepubliceerd' : ' — privé');
      $form['previews'][$media->id()] = ['#type' => 'container', 'image' => ['#theme' => 'image', '#uri' => $file->getFileUri(), '#alt' => $media->label()], 'name' => ['#plain_text' => $file->getFilename()]];
      if ($public) { $form['previews'][$media->id()]['link'] = ['#type' => 'textfield', '#title' => 'Openbare foto-URL', '#value' => $public['url'], '#maxlength' => 2048, '#attributes' => ['readonly' => 'readonly']]; }
    }
    if ($options) {
      $form['photos'] = ['#type' => 'checkboxes', '#title' => 'Foto’s publiceren', '#options' => $options, '#required' => TRUE];
      $form['new_version'] = ['#type' => 'checkbox', '#title' => 'Nieuwe versie maken voor al gepubliceerde foto’s', '#description' => 'Elke nieuwe versie krijgt een nieuwe URL. Bestaande kopieën blijven beschikbaar voor eerder verzonden nieuwsbrieven.'];
      $form['confirm'] = ['#type' => 'checkbox', '#title' => 'Ik bevestig dat deze geselecteerde foto’s openbaar gedeeld mogen worden.', '#required' => TRUE];
      $form['actions']['#type'] = 'actions';
      $form['actions']['submit'] = ['#type' => 'submit', '#value' => 'Geselecteerde foto’s publiceren'];
    }
    else { $form['empty'] = ['#markup' => '<p>Dit artikel heeft geen foto’s.</p>']; }
    $form['back'] = Link::fromTextAndUrl('Terug naar het artikel', $newsletter_article->toUrl())->toRenderable();
    return $form;
  }
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $article = NewsletterArticle::load($form_state->get('article_id'));
    $selection = array_filter($form_state->getValue('photos'));
    $count = 0;
    foreach ($article->get('photos')->referencedEntities() as $media) {
      if (!isset($selection[$media->id()])) { continue; }
      try { PublicPhotos::publish($media, (bool) $form_state->getValue('new_version')); $count++; }
      catch (\Throwable $exception) {
        \Drupal::logger('ksa_newsletter')->error('Publiceren nieuwsbriefafbeelding @id mislukt: @type', ['@id' => $media->id(), '@type' => get_class($exception)]);
        $this->messenger()->addError('Een foto kon niet worden gepubliceerd. Probeer die foto opnieuw.');
      }
    }
    if ($count) { $this->messenger()->addStatus($this->t('@count foto(’s) gepubliceerd. De openbare URL’s staan hieronder en zijn beschikbaar via de API.', ['@count' => $count])); }
    $form_state->setRedirect('ksa_newsletter.publish_photos', ['newsletter_article' => $article->id()]);
  }
}
