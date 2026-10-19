<?php
namespace Drupal\ksa_newsletter\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/** Standard configurable field widgets; every admin edit retains a revision. */
final class ArticleForm extends ContentEntityForm {
  protected function getNewRevisionDefault() { return TRUE; }

  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['revision']['#disabled'] = TRUE;
    $form['revision']['#description'] = 'Elke wijziging bewaart automatisch een nieuwe revisie.';
    return $form;
  }

  public function save(array $form, FormStateInterface $form_state) {
    $this->entity->setNewRevision(TRUE);
    $result = $this->entity->save();
    $this->messenger()->addStatus('Het nieuwsbriefartikel is opgeslagen. De vorige versie blijft beschikbaar bij Revisies.');
    $form_state->setRedirect('entity.newsletter_article.canonical', ['newsletter_article' => $this->entity->id()]);
    return $result;
  }
}
