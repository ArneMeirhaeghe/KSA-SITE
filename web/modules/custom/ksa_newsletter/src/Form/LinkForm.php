<?php
namespace Drupal\ksa_newsletter\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\ksa_newsletter\SubmissionGate;
final class LinkForm extends FormBase {
  public function getFormId() { return 'ksa_newsletter_link'; }
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#cache']['max-age'] = 0;
    $token = SubmissionGate::token();
    $form['intro'] = ['#markup' => '<p>Deel deze link alleen met leiding en oud-leiding. De link geeft uitsluitend toegang tot het insturen, niet tot de verzamelde bijdragen. Een nieuwe link maakt ook eerder geopende instuursessies ongeldig.</p>'];
    if ($token !== '') {
      $form['link'] = ['#type' => 'textfield', '#title' => 'Gedeelde instuurlink', '#value' => Url::fromRoute('ksa_newsletter.entry', [], ['absolute' => TRUE, 'query' => ['toegang' => $token]])->toString(), '#attributes' => ['readonly' => 'readonly'], '#maxlength' => 1024];
      $form['confirm'] = ['#type' => 'checkbox', '#title' => 'Ik wil de bestaande link en open instuursessies ongeldig maken.', '#required' => TRUE];
    }
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = ['#type' => 'submit', '#value' => $token === '' ? 'Instuurlink aanmaken' : 'Instuurlink vervangen'];
    return $form;
  }
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    SubmissionGate::rotate();
    $this->messenger()->addStatus('De nieuwe instuurlink is klaar. Deel hem met de schrijvers.');
    $form_state->setRedirect('ksa_newsletter.settings');
  }
}
