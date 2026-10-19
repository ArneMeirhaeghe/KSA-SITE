<?php
namespace Drupal\ksa_newsletter;
use Drupal\Component\Utility\Crypt;
use Symfony\Component\HttpFoundation\Request;

/** Revocable capability, never a grant to read or administer contributions. */
final class SubmissionGate {
  public static function token(): string {
    return (string) \Drupal::state()->get('ksa_newsletter.token', '');
  }
  public static function rotate(): string {
    $token = Crypt::randomBytesBase64(32);
    \Drupal::state()->set('ksa_newsletter.token', $token);
    return $token;
  }
  public static function allowed(Request $request): bool {
    $account = \Drupal::currentUser();
    if ($account->hasPermission('administer newsletter articles') || $account->hasPermission('submit newsletter articles')) {
      return TRUE;
    }
    $token = self::token();
    return $token !== '' && $request->hasSession() && hash_equals(hash('sha256', $token), (string) $request->getSession()->get('ksa_newsletter.capability', ''));
  }
  public static function accept(Request $request, string $token): bool {
    $current = self::token();
    if ($current === '' || !hash_equals($current, $token)) {
      return FALSE;
    }
    $request->getSession()->set('ksa_newsletter.capability', hash('sha256', $current));
    return TRUE;
  }
  public static function identifier(Request $request): string {
    return hash_hmac('sha256', $request->getClientIp() ?? '', \Drupal::service('private_key')->get());
  }
  public static function allowedAttempt(Request $request): bool {
    $flood = \Drupal::service('flood');
    $id = self::identifier($request);
    return $flood->isAllowed('ksa_newsletter.hour', (int) Settings::get('submissions_per_hour'), 3600, $id)
      && $flood->isAllowed('ksa_newsletter.day', (int) Settings::get('submissions_per_day'), 86400, $id)
      && $flood->isAllowed('ksa_newsletter.global', (int) Settings::get('submissions_total_per_day'), 86400, 'all');
  }
  public static function registerAttempt(Request $request): void {
    $flood = \Drupal::service('flood');
    $id = self::identifier($request);
    $flood->register('ksa_newsletter.hour', 3600, $id);
    $flood->register('ksa_newsletter.day', 86400, $id);
    $flood->register('ksa_newsletter.global', 86400, 'all');
  }
}
