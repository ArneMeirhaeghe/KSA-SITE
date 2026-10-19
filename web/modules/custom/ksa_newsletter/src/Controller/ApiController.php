<?php
namespace Drupal\ksa_newsletter\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\ksa_newsletter\ApiAccess;
use Drupal\ksa_newsletter\NewsletterText;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

final class ApiController extends ControllerBase {
  public function articles(Request $request) {
    if ($error = ApiAccess::check($request)) { return $error; }
    $query = $this->entityTypeManager()->getStorage('newsletter_article')->getQuery()->accessCheck(FALSE)->sort('id');
    $params = [];
    $dates = [];
    foreach (['van', 'tot'] as $key) {
      $value = $request->query->all()[$key] ?? '';
      if (!is_string($value)) { return $this->badRequest(); }
      if ($value === '') { continue; }
      $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value, new \DateTimeZone('Europe/Brussels'));
      if (!$date || $date->format('Y-m-d') !== $value) { return $this->badRequest(); }
      $dates[$key] = $date;
      $params[$key] = $value;
      $query->condition('created', ($key === 'tot' ? $date->modify('+1 day') : $date)->getTimestamp(), $key === 'tot' ? '<' : '>=');
    }
    if (isset($dates['van'], $dates['tot']) && $dates['van'] > $dates['tot']) { return $this->badRequest(); }
    foreach (['limit' => 50, 'after' => 0] as $key => $default) {
      $value = $request->query->all()[$key] ?? (string) $default;
      if (!is_string($value) || !preg_match('/^\d{1,10}$/D', $value)) { return $this->badRequest(); }
      $params[$key] = (int) $value;
    }
    if ($params['limit'] < 1 || $params['limit'] > 100) { return $this->badRequest(); }
    $query->condition('id', $params['after'], '>')->range(0, $params['limit'] + 1);
    $storage = $this->entityTypeManager()->getStorage('newsletter_article');
    $ids = array_values($query->execute());
    $more = count($ids) > $params['limit'];
    $ids = array_slice($ids, 0, $params['limit']);
    $data = [];
    foreach ($storage->loadMultiple($ids) as $article) {
      $photos = [];
      foreach ($article->get('photos')->referencedEntities() as $media) {
        if ($media->bundle() !== 'newsletter_photo' || !$file = $media->get('field_newsletter_image')->entity) { continue; }
        $image = $media->get('field_newsletter_image')->first();
        $url = Url::fromRoute('ksa_newsletter.api_photo', ['article_uuid' => $article->uuid(), 'media_uuid' => $media->uuid()], ['absolute' => TRUE])->toString();
        $public = \Drupal\ksa_newsletter\PublicPhotos::get($media);
        $photos[] = ['id' => $media->uuid(), 'filename' => $file->getFilename(), 'mime_type' => $file->getMimeType(), 'size_bytes' => (int) $file->getSize(), 'width' => (int) $image->width, 'height' => (int) $image->height, 'url' => $url, 'download_url' => $url, 'requires_authorization' => TRUE, 'public_url' => $public['url'] ?? NULL, 'public_image' => $public];
      }
      $body = (string) $article->get('body')->value;
      $data[] = ['id' => $article->uuid(), 'title' => $article->label(), 'writer' => $article->get('writer')->value, 'text_html' => NewsletterText::render($article), 'submitted_at' => gmdate('Y-m-d\TH:i:s\Z', $article->get('created')->value), 'activity_date' => $article->get('activity_date')->value ?: NULL, 'photos' => $photos];
    }
    $next = NULL;
    if ($more && $ids) {
      $params['after'] = (int) end($ids);
      $next = Url::fromRoute('ksa_newsletter.api_articles', [], ['absolute' => TRUE, 'query' => $params])->toString();
    }
    return new JsonResponse(['schema_version' => 1, 'data' => $data, 'pagination' => ['count' => count($data), 'next' => $next]]);
  }

  public function photo(Request $request, string $article_uuid, string $media_uuid) {
    if ($error = ApiAccess::check($request)) { return $error; }
    $articles = $this->entityTypeManager()->getStorage('newsletter_article')->loadByProperties(['uuid' => $article_uuid]);
    foreach ($articles as $article) {
      foreach ($article->get('photos')->referencedEntities() as $media) {
        if ($media->uuid() !== $media_uuid || $media->bundle() !== 'newsletter_photo') { continue; }
        $file = $media->get('field_newsletter_image')->entity;
        if (!$file || !str_starts_with($file->getFileUri(), 'private://newsletter/')) { continue; }
        $path = \Drupal::service('file_system')->realpath($file->getFileUri());
        $root = \Drupal::service('file_system')->realpath('private://newsletter');
        if (!$path || !$root || !str_starts_with($path, $root . DIRECTORY_SEPARATOR) || !is_file($path)) { continue; }
        $response = new BinaryFileResponse($path, 200, ['Content-Type' => $file->getMimeType(), 'X-Content-Type-Options' => 'nosniff'], FALSE, NULL, FALSE, FALSE);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file->getFilename(), 'newsletter-photo.' . pathinfo($path, PATHINFO_EXTENSION));
        return $response;
      }
    }
    return new JsonResponse(['error' => 'not_found'], 404);
  }

  private function badRequest(): JsonResponse {
    return new JsonResponse(['error' => 'invalid_parameters', 'message' => 'Gebruik van/tot als YYYY-MM-DD, limit van 1 tot 100 en after als positief geheel getal.'], 400);
  }
}
