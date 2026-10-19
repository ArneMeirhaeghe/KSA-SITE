<?php
namespace Drupal\ksa_newsletter;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/** Independent, read-only credential. Never accepts cookies or the submit link. */
final class ApiAccess {
  public static function check(Request $request): ?JsonResponse {
    if (!$request->isSecure()) {
      return new JsonResponse(['error' => 'https_required', 'message' => 'Gebruik HTTPS voor deze API.'], 400);
    }
    $flood = \Drupal::service('flood');
    $identifier = SubmissionGate::identifier($request);
    if (!$flood->isAllowed('ksa_newsletter.api', 120, 60, $identifier)) {
      return new JsonResponse(['error' => 'rate_limit', 'message' => 'Probeer over een minuut opnieuw.'], 429, ['Retry-After' => '60']);
    }
    $flood->register('ksa_newsletter.api', 60, $identifier);
    $header = $request->headers->get('Authorization', '');
    $hash = \Drupal::state()->get('ksa_newsletter.api_hash', '');
    if (!preg_match('/^Bearer (ksa_nl_[a-f0-9]{64})$/D', $header, $match) || !$hash || !hash_equals($hash, hash('sha256', $match[1]))) {
      return new JsonResponse(['error' => 'unauthorized', 'message' => 'Een geldige API-toegangssleutel is vereist.'], 401, ['WWW-Authenticate' => 'Bearer realm="KSA Nieuwsbrief"']);
    }
    return NULL;
  }
}
