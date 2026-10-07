<?php

namespace Drupal\ksa_editor\EventSubscriber;

use Drupal\Core\Routing\LocalRedirectResponse;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/** Replaces the editor's own empty profile landing page with useful tasks. */
final class EditorLanding implements EventSubscriberInterface {

  public function __construct(private AccountProxyInterface $account) {}

  public static function getSubscribedEvents(): array {
    return [KernelEvents::RESPONSE => ['onResponse', 10]];
  }

  public function onResponse(ResponseEvent $event): void {
    if (!$event->isMainRequest() || !ksa_editor_is_editor($this->account)) {
      return;
    }
    $request = $event->getRequest();
    if ($request->attributes->get('_route') !== 'entity.user.canonical' || !$request->isMethod('GET') || $event->getResponse()->getStatusCode() !== 200) {
      return;
    }
    $user = $request->attributes->get('user');
    if ($user && (string) $user->id() === (string) $this->account->id()) {
      $response = new LocalRedirectResponse(Url::fromRoute('ksa_editor.home')->toString());
      $response->getCacheableMetadata()->setCacheMaxAge(0);
      $event->setResponse($response);
    }
  }

}
