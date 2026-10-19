<?php
namespace Drupal\ksa_newsletter\Form;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ksa_newsletter\Settings;

final class SettingsForm extends ConfigFormBase {
  public function getFormId() { return 'ksa_newsletter_configuration'; }
  protected function getEditableConfigNames() { return ['ksa_newsletter.settings']; }
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['help'] = ['#markup' => '<p>Beheer extra velden en de formulier- en artikelweergave via de tabbladen hierboven. De opmaakknoppen beheer je bij <a href="/admin/config/content/formats">Tekstformaten en editors</a>. Het overzicht beheer je bij <a href="/admin/structure/views/view/newsletter_articles">Overzichten → Nieuwsbriefartikelen</a>.</p>'];
    $options = [];
    foreach (\Drupal::entityTypeManager()->getStorage('filter_format')->loadMultiple() as $format) {
      if ($format->status() && !empty($format->filters()->get('filter_html')->status)) { $options[$format->id()] = $format->label(); }
    }
    $form['text_format'] = ['#type' => 'select', '#title' => 'Tekstformaat voor nieuwe inzendingen', '#options' => $options, '#default_value' => Settings::get('text_format'), '#required' => TRUE, '#description' => 'Alleen formaten met HTML-beperking zijn toegestaan. Geef inzenders gebruiksrecht op dit formaat bij Tekstformaten en editors. Bestaande artikelen behouden hun opgeslagen formaat.'];
    foreach (['photo_max_width' => ['Maximale breedte openbare foto', 4000], 'photo_max_height' => ['Maximale hoogte openbare foto', 4000], 'api_requests_per_minute' => ['API-aanvragen per minuut per IP', 1000], 'submissions_per_hour' => ['Instuurpogingen per uur per IP', 100], 'submissions_per_day' => ['Instuurpogingen per dag per IP', 1000], 'submissions_total_per_day' => ['Instuurpogingen per dag voor de hele site', 10000]] as $key => [$label, $max]) {
      $form[$key] = ['#type' => 'number', '#title' => $label, '#min' => 1, '#max' => $max, '#required' => TRUE, '#default_value' => Settings::get($key)];
    }
    $form['photo_help'] = ['#markup' => '<p>Fotoafmetingen gelden alleen voor nieuwe openbare kopieën. Reeds verzonden foto-URL’s en afmetingen blijven behouden.</p>'];
    return parent::buildForm($form, $form_state);
  }
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $config = $this->config('ksa_newsletter.settings');
    foreach (['text_format', 'photo_max_width', 'photo_max_height', 'api_requests_per_minute', 'submissions_per_hour', 'submissions_per_day', 'submissions_total_per_day'] as $key) {
      $config->set($key, $key === 'text_format' ? $form_state->getValue($key) : (int) $form_state->getValue($key));
    }
    $config->save();
    parent::submitForm($form, $form_state);
  }
}
