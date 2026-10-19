<?php
namespace Drupal\ksa_newsletter;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
final class NewsletterAccess extends EntityAccessControlHandler {
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    return AccessResult::allowedIf($account->hasPermission('administer newsletter articles'))->cachePerPermissions();
  }
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    // Anonymous creation is exclusively through the guarded submission form.
    return AccessResult::allowedIf($account->hasPermission('administer newsletter articles'))->cachePerPermissions();
  }
}
