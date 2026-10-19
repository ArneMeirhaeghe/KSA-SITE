<?php
namespace Drupal\ksa_newsletter;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
final class NewsletterAccess extends EntityAccessControlHandler {
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    $permission = match ($operation) {
      'view', 'view revision', 'view all revisions' => 'view newsletter articles',
      'update', 'revert', 'revert revision' => 'edit newsletter articles',
      'delete', 'delete revision' => 'delete newsletter articles',
      default => 'administer newsletter articles',
    };
    return AccessResult::allowedIfHasPermissions($account, ['administer newsletter articles', $permission], 'OR');
  }
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'administer newsletter articles');
  }
}
