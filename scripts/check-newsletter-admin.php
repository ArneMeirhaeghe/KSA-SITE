<?php
/** Integration checks without retaining test entities or permission changes. */
use Drupal\Core\Session\UserSession;
use Drupal\Core\Session\AnonymousUserSession;
use Drupal\ksa_newsletter\NewsletterText;
use Drupal\ksa_newsletter\Entity\NewsletterArticle;

$tx = \Drupal::database()->startTransaction();
$count = 0;
$assert = function ($test, $label) use (&$count) { if (!$test) { throw new RuntimeException($label); } $count++; };
$storage = \Drupal::entityTypeManager()->getStorage('newsletter_article');
try {
  $type = \Drupal::entityTypeManager()->getDefinition('newsletter_article');
  $assert($type->isRevisionable() && $type->isInternal(), 'Revisions enabled, generic JSON API still excluded');
  $assert($type->get('field_ui_base_route') === 'ksa_newsletter.configuration', 'Field UI configured');
  $article = NewsletterArticle::create(['title' => 'ADMIN TEST original', 'writer' => 'Synthetic', 'content' => ['value' => '<p><strong>Veilig</strong><img src=x onerror=alert(1)><a href="javascript:alert(1)">link</a></p>', 'format' => 'newsletter']]);
  $article->save();
  $id = $article->id(); $first = $article->getRevisionId();
  $article->setNewRevision(TRUE); $article->set('title', 'ADMIN TEST edited'); $article->save();
  $storage->resetCache([$id]);
  $assert($storage->load($id)->label() === 'ADMIN TEST edited', 'Current revision saved');
  $assert($storage->loadRevision($first)->label() === 'ADMIN TEST original', 'Old text retained');
  $old = $storage->loadRevision($first); $old->setNewRevision(TRUE); $old->isDefaultRevision(TRUE); $old->save();
  $storage->resetCache([$id]);
  $assert($storage->load($id)->label() === 'ADMIN TEST original', 'Revision can be restored');
  $assert($storage->getQuery()->allRevisions()->accessCheck(FALSE)->condition('id', $id)->count()->execute() === '3' || count($storage->getQuery()->allRevisions()->accessCheck(FALSE)->condition('id', $id)->execute()) === 3, 'Restore retains all versions');
  $html = NewsletterText::render($article);
  $assert(str_contains($html, '<strong>Veilig</strong>') && !str_contains($html, '<img') && !str_contains($html, 'javascript:'), 'Drupal filters preserve formatting and strip XSS');
  foreach (['view newsletter articles', 'edit newsletter articles', 'delete newsletter articles', 'publish newsletter photos', 'administer newsletter api', 'submit newsletter articles'] as $i => $permission) {
    $account = new class($permission, $i) extends UserSession {
      private string $permission;
      public function __construct($permission, $i) { parent::__construct(['uid' => 910000 + $i, 'roles' => []]); $this->permission = $permission; }
      public function hasPermission($permission) { return $permission === $this->permission; }
    };
    foreach (['view' => 'view newsletter articles', 'update' => 'edit newsletter articles', 'delete' => 'delete newsletter articles', 'revert' => 'edit newsletter articles'] as $op => $expected) {
      $assert(($op === 'revert' ? $storage->loadRevision($first) : $article)->access($op, $account) === ($permission === $expected), 'Granular permission ' . $permission . '/' . $op);
    }
  }
  $anon = new AnonymousUserSession();
  foreach (['ksa_newsletter.overview', 'ksa_newsletter.api_settings', 'ksa_newsletter.configuration', 'ksa_newsletter.publish_photos', 'entity.newsletter_article.edit_form', 'entity.newsletter_article.version_history'] as $route) {
    $assert(!\Drupal::service('access_manager')->checkNamedRoute($route, ['newsletter_article' => $article->id()], $anon), 'Anonymous route denied: ' . $route);
  }
  $view = \Drupal\views\Views::getView('newsletter_articles');
  $view->setDisplay('default'); $view->setExposedInput(['title' => 'ADMIN TEST original']); $view->execute();
  $assert(count($view->result) === 1 && $view->result[0]->_entity->id() === $id, 'Views filter finds the article');
  foreach ($view->field as $handler) { $assert(!($handler instanceof \Drupal\views\Plugin\views\field\Broken), 'Views field plugin valid'); }
  $assert(\Drupal::config('editor.editor.newsletter')->get('editor') === 'ckeditor5', 'Standard Drupal editor');
  foreach ($storage->loadMultiple($storage->getQuery()->accessCheck(FALSE)->execute()) as $saved) {
    $assert(!$saved->get('content')->isEmpty() && $saved->getRevisionCreationTime() > 0, 'Migrated article retains text and revision date');
  }
  echo "PASS: $count newsletter admin, revision, Views and permission checks.\n";
}
finally { $tx->rollBack(); }
