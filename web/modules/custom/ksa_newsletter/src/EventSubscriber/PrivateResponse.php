<?php
namespace Drupal\ksa_newsletter\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
final class PrivateResponse implements EventSubscriberInterface {
  public static function getSubscribedEvents(): array { return [KernelEvents::RESPONSE => ['protect', -1000]]; }
  public function protect(ResponseEvent $event): void {
    $route = (string) $event->getRequest()->attributes->get('_route', '');
    if (str_starts_with($route, 'ksa_newsletter.') || str_starts_with($route, 'entity.newsletter_article.')) {
      $response = $event->getResponse();
      $response->headers->set('Cache-Control', 'private, no-store');
      $response->headers->set('Referrer-Policy', 'no-referrer');
      $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }
  }
}
