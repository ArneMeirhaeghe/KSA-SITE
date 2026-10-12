<?php

/** Audit regression checks: isolated, unsaved fixtures; all log writes rolled back. */
use Drupal\Core\Session\UserSession;
use Drupal\ksa_editor\LeadershipLog;

$db = \Drupal::database();
$transaction = $db->startTransaction();
$switcher = \Drupal::service('account_switcher');
$switcher->switchTo(new UserSession(['uid' => 999999, 'name' => 'audit-fixture', 'roles' => ['authenticated', 'content_editor']]));
$storage = \Drupal::entityTypeManager()->getStorage('entity_log');
$checks = 0;
$assert = static function (bool $condition, string $message) use (&$checks): void {
  $checks++;
  if (!$condition) {
    throw new RuntimeException($message);
  }
};
$count = static fn () => (int) $storage->getQuery()->accessCheck(FALSE)->condition('log_type', 'ksa_leiding')->count()->execute();
$initial = $count();
try {
  $block = \Drupal::entityTypeManager()->getStorage('block_content')->create(['id' => 999999, 'type' => 'hero', 'info' => 'Audit test', 'field_heading' => 'Oude titel']);
  $original = clone $block;
  $block->set('field_heading', '<script>alert("test")</script>Nieuwe titel');
  $block->setOriginal($original);
  LeadershipLog::record($block, 'update');
  $assert($count() === $initial + 1, 'One changed field must create exactly one row.');
  $id = $storage->getQuery()->accessCheck(FALSE)->sort('id', 'DESC')->range(0, 1)->execute();
  $log = $storage->load(reset($id));
  $old = json_decode($log->getOldValue(), TRUE);
  $new = json_decode($log->getNewValue(), TRUE);
  $assert($old['values'][0]['value'] === 'Oude titel', 'Original text must survive.');
  $assert($new['values'][0]['value'] === '<script>alert("test")</script>Nieuwe titel', 'Full new text must survive.');
  $assert((int) $log->getOwnerId() === 999999 && $new['context']['actor'] === 'audit-fixture', 'Actor must be the editing account.');
  $block->setOriginal(clone $block);
  LeadershipLog::record($block, 'update');
  $assert($count() === $initial + 1, 'Saving identical values must not add noise.');
  // Exercise real storage hooks, not only the snapshot helper.
  $test_block = \Drupal::entityTypeManager()->getStorage('block_content')->create(['type' => 'hero', 'info' => 'Transactional audit fixture', 'field_heading' => 'Eerste tekst']);
  $test_block->save();
  $after_insert = $count();
  $test_block->set('field_heading', 'Tweede tekst');
  $test_block->save();
  $assert($count() === $after_insert + 1, 'Real entity saves must invoke the audit hook.');
  $images = array_values(\Drupal::entityTypeManager()->getStorage('media')->loadByProperties(['bundle' => 'image']));
  $test_block->set('field_images', [['target_id' => $images[0]->id()], ['target_id' => $images[1]->id()]]);
  $test_block->save();
  $old_images = LeadershipLog::value($test_block, 'field_images');
  $test_block->set('field_images', [['target_id' => $images[1]->id()], ['target_id' => $images[0]->id()]]);
  $test_block->save();
  $latest = $storage->getQuery()->accessCheck(FALSE)->sort('id', 'DESC')->range(0, 1)->execute();
  $image_log = $storage->load(reset($latest));
  $assert(json_decode($image_log->getOldValue(), TRUE)['values'] === $old_images, 'Photo order and resolved original names must survive replacement.');
  $assert(json_decode($image_log->getNewValue(), TRUE)['values'][0]['target_id'] == $images[1]->id(), 'Photo reorder must be logged.');
  LeadershipLog::record($block, 'delete');
  $assert($count() > $initial + 1, 'Removal must retain a before snapshot.');
  $user = \Drupal::entityTypeManager()->getStorage('user')->create(['uid' => 999998, 'name' => 'fixture', 'pass' => 'do-not-record-this', 'mail' => 'fixture@example.invalid']);
  LeadershipLog::record($user, 'insert');
  foreach ($storage->loadMultiple($storage->getQuery()->accessCheck(FALSE)->condition('user_id', 999999)->execute()) as $entry) {
    $assert(!str_contains($entry->getNewValue(), 'do-not-record-this'), 'Passwords must never enter audit storage.');
  }
  foreach (['image', 'document'] as $bundle) {
    $media = \Drupal::entityTypeManager()->getStorage('media')->loadByProperties(['bundle' => $bundle]);
    if (!$media) {
      throw new RuntimeException("No $bundle fixture available.");
    }
    $item = reset($media);
    $field = $item->getSource()->getConfiguration()['source_field'];
    $snapshot = LeadershipLog::value($item, $field);
    $assert(!empty($snapshot[0]['bestand']) && !empty($snapshot[0]['locatie']), "$bundle snapshot must contain the filename and URI.");
  }
  $access = \Drupal::service('access_manager');
  $assert(!$access->checkNamedRoute('ksa_editor.audit', [], \Drupal::currentUser()), 'Leadership must not read the audit log.');
  $assert(!$storage->getEntityType()->getAdminPermission() || !\Drupal::currentUser()->hasPermission($storage->getEntityType()->getAdminPermission()), 'Leadership must not manage stored logs.');
  $switcher->switchTo(new UserSession(['uid' => 1, 'roles' => ['authenticated', 'administrator']]));
  $assert($access->checkNamedRoute('ksa_editor.audit', [], \Drupal::currentUser()), 'Administrator must read the audit log.');
  $before_admin = $count();
  LeadershipLog::record($block, 'insert');
  $assert($count() === $before_admin, 'Admin-only activity must not be attributed to leadership.');
  $controller = new \Drupal\ksa_editor\Controller\LeadershipLogController();
  $build = $controller->overview(\Symfony\Component\HttpFoundation\Request::create('/admin/reports/leiding'));
  $html = (string) \Drupal::service('renderer')->renderInIsolation($build);
  $assert(!str_contains($html, '<script>alert'), 'Historic text must not execute as HTML.');
  $assert(str_contains($html, 'Nieuwe titel'), 'Audit overview must render stored changes.');
  foreach ([['gebruiker' => 123456789], ['type' => 'file'], ['zoek' => 'nonexistent-audit-subject'], ['vanaf' => '2099-01-01'], ['tot' => '1970-01-01']] as $filters) {
    $filtered = $controller->overview(\Symfony\Component\HttpFoundation\Request::create('/admin/reports/leiding', 'GET', $filters));
    $assert(!str_contains((string) \Drupal::service('renderer')->renderInIsolation($filtered), 'Nieuwe titel'), 'Each filter must exclude unmatched events.');
  }
  $switcher->switchBack();
  printf("%d audit checks passed. Transaction rolled back; no content, users or logs retained.\n", $checks);
}
finally {
  $switcher->switchBack();
  $transaction->rollBack();
}
