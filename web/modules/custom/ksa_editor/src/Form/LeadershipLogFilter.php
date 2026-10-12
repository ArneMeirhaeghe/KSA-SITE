<?php

namespace Drupal\ksa_editor\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

final class LeadershipLogFilter extends FormBase {

  public function getFormId(): string {
    return 'ksa_leadership_log_filter';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $request = $this->getRequest();
    $form['#method'] = 'get';
    $form['#token'] = FALSE;
    $form['#attributes']['class'][] = 'ksa-log-filter';
    $form['zoek'] = ['#type' => 'textfield', '#title' => 'Onderdeel of veld', '#default_value' => $request->query->get('zoek', ''), '#size' => 25];
    $actors = ['' => 'Alle gebruikers'];
    $uids = \Drupal::database()->select('entity_log', 'l')->fields('l', ['user_id'])->condition('log_type', 'ksa_leiding')->distinct()->execute()->fetchCol();
    foreach ($uids as $uid) {
      $user = \Drupal::entityTypeManager()->getStorage('user')->load($uid);
      $actors[$uid] = ($user ? $user->getAccountName() : 'Verwijderde gebruiker') . ' (#' . $uid . ')';
    }
    $form['gebruiker'] = ['#type' => 'select', '#title' => 'Gebruiker', '#options' => $actors, '#default_value' => $request->query->get('gebruiker', '')];
    $form['type'] = ['#type' => 'select', '#title' => 'Soort', '#options' => ['' => 'Alles', 'node' => 'Pagina’s, nieuws en leiding', 'block_content' => 'Tekst- en fotoblokken', 'media' => 'Foto’s en PDF’s', 'file' => 'Bestanden', 'user' => 'Eigen profiel'], '#default_value' => $request->query->get('type', '')];
    $form['vanaf'] = ['#type' => 'date', '#title' => 'Vanaf', '#default_value' => $request->query->get('vanaf', '')];
    $form['tot'] = ['#type' => 'date', '#title' => 'Tot en met', '#default_value' => $request->query->get('tot', '')];
    $form['submit'] = ['#type' => 'submit', '#value' => 'Filteren'];
    $form['reset'] = ['#type' => 'link', '#title' => 'Filters wissen', '#url' => \Drupal\Core\Url::fromRoute('ksa_editor.audit')];
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $query = array_filter(array_intersect_key($form_state->getValues(), array_flip(['zoek', 'gebruiker', 'type', 'vanaf', 'tot'])), static fn ($value) => $value !== '' && $value !== NULL);
    $form_state->setRedirect('ksa_editor.audit', [], ['query' => $query]);
  }

}
