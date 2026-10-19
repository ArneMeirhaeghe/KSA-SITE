<?php
namespace Drupal\ksa_newsletter\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

final class ApiKeyForm extends FormBase {
  public function getFormId() { return 'ksa_newsletter_api_key'; }
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#cache']['max-age'] = 0;
    $active = (bool) \Drupal::state()->get('ksa_newsletter.api_hash');
    $form['intro'] = ['#markup' => '<p>Een aparte sleutel geeft de nieuwsbriefapp alleen leestoegang tot alle nieuwsbriefartikelen en hun privéfoto’s. Verstuur hem uitsluitend als <code>Authorization: Bearer …</code> over HTTPS. Bewaar hem veilig in de app, nooit in een URL.</p>'];
    $keys = $this->getRequest()->getSession()->getFlashBag()->get('ksa_newsletter.api_key');
    if ($keys) {
      $form['key'] = ['#type' => 'textfield', '#title' => 'Nieuwe API-sleutel — kopieer hem nu', '#value' => $keys[0], '#attributes' => ['readonly' => 'readonly', 'autocomplete' => 'off'], '#description' => 'Deze sleutel wordt één keer getoond. Drupal bewaart alleen een hash.'];
    }
    $form['status'] = ['#markup' => $active ? '<p>API-toegang is actief.</p>' : '<p>Er is nog geen actieve API-sleutel.</p>'];
    if ($active) {
      $form['confirm'] = ['#type' => 'checkbox', '#title' => 'Ik begrijp dat de bestaande sleutel onmiddellijk ongeldig wordt.', '#required' => TRUE];
    }
    $form['actions']['#type'] = 'actions';
    $form['actions']['create'] = ['#type' => 'submit', '#value' => $active ? 'Sleutel vervangen' : 'API-sleutel aanmaken'];
    if ($active) { $form['actions']['revoke'] = ['#type' => 'submit', '#value' => 'API-toegang intrekken', '#name' => 'revoke']; }
    return $form;
  }
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    if (($form_state->getTriggeringElement()['#name'] ?? '') === 'revoke') {
      \Drupal::state()->delete('ksa_newsletter.api_hash');
      $this->messenger()->addStatus('API-toegang is ingetrokken.');
    }
    else {
      $key = 'ksa_nl_' . bin2hex(random_bytes(32));
      \Drupal::state()->set('ksa_newsletter.api_hash', hash('sha256', $key));
      $this->getRequest()->getSession()->getFlashBag()->add('ksa_newsletter.api_key', $key);
      $this->messenger()->addStatus('De API-sleutel is klaar. Kopieer hem nu naar de nieuwsbriefapp.');
    }
    $form_state->setRedirect('ksa_newsletter.api_settings');
  }
}
