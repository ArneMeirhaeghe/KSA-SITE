<?php
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\ksa_newsletter\PublicPhotos;
use Drupal\Core\File\FileSystemInterface;

$transaction = \Drupal::database()->startTransaction();
$state = \Drupal::state();
$old_key = $state->get('ksa_newsletter.api_hash');
$paths = [];
$count = 0;
$assert = function ($condition, $label) use (&$count) { if (!$condition) { throw new RuntimeException($label); } $count++; };
$directory = 'private://newsletter/' . \Drupal::service('uuid')->generate();
try {
  \Drupal::service('file_system')->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);
  $uri = $directory . '/fixture.png';
  $paths[] = $uri;
  $image = imagecreatetruecolor(2400, 1800);
  imagefill($image, 0, 0, imagecolorallocate($image, 84, 196, 78));
  imagepng($image, $uri);
  $file = File::create(['uri' => $uri, 'status' => 1]); $file->save();
  $media = Media::create(['bundle' => 'newsletter_photo', 'name' => 'TEST temporary public image', 'status' => 0, 'field_newsletter_image' => ['target_id' => $file->id(), 'alt' => 'Synthetic test']]); $media->save();
  $original = hash_file('sha256', $uri);
  $assert(PublicPhotos::get($media) === NULL, 'No implicit public URL');
  $article = \Drupal::entityTypeManager()->getStorage('newsletter_article')->create(['title' => 'Publication API test', 'writer' => 'Synthetic', 'body' => 'Test', 'photos' => [['target_id' => $media->id()]]]);
  $article->save();
  $key = 'ksa_nl_' . bin2hex(random_bytes(32));
  $state->set('ksa_newsletter.api_hash', hash('sha256', $key));
  $request = \Symfony\Component\HttpFoundation\Request::create('https://ksa.ddev.site/api/nieuwsbrief/artikelen', 'GET', ['after' => (string) ($article->id() - 1)]);
  $request->server->set('REMOTE_ADDR', '192.0.2.242');
  $request->headers->set('Authorization', 'Bearer ' . $key);
  $controller = new \Drupal\ksa_newsletter\Controller\ApiController();
  $get_photo = fn () => json_decode($controller->articles($request)->getContent(), TRUE)['data'][0]['photos'][0];
  $assert($get_photo()['public_url'] === NULL, 'API unpublished photo has no public URL');
  $first = PublicPhotos::publish($media);
  $record1 = \Drupal::keyValue('ksa_newsletter.public_photos')->get($media->uuid()); $paths[] = $record1['uri'];
  $assert($first['width'] === 1600 && $first['height'] === 1200, 'Scaled, not cropped');
  $assert(str_contains($first['url'], '/newsletter/' . $first['id'] . '.png'), 'UUID URL');
  $assert($original === hash_file('sha256', $uri), 'Private original untouched');
  $assert(!$media->isPublished(), 'Original Media still unpublished');
  $assert(PublicPhotos::publish($media)['url'] === $first['url'], 'Repeated publishing reuses URL');
  $first_hash = hash_file('sha256', $record1['uri']);
  $second = PublicPhotos::publish($media, TRUE);
  $record2 = \Drupal::keyValue('ksa_newsletter.public_photos')->get($media->uuid()); $paths[] = $record2['uri'];
  $assert($second['url'] !== $first['url'], 'New version gets new URL');
  $assert(is_file($record1['uri']) && hash_file('sha256', $record1['uri']) === $first_hash, 'Old public copy retained');
  $public_media = Media::load($record2['media_id']);
  $assert($public_media->bundle() === 'image' && $public_media->isPublished(), 'Copy stored through Media');
  $assert(PublicPhotos::get($media)['id'] === $second['id'], 'Latest public version returned');
  $photo = $get_photo();
  $assert($photo['public_url'] === $second['url'] && $photo['public_image'] === $second, 'API returns latest public URL and image metadata');
  $assert($photo['requires_authorization'] && $photo['download_url'] !== $photo['public_url'], 'Original download remains separate and protected');
  echo "PASS: $count public photo publication checks; synthetic files removed.\n";
}
finally {
  foreach ($paths as $path) { if (file_exists($path)) { \Drupal::service('file_system')->delete($path); } }
  if (is_dir($directory)) { rmdir($directory); }
  $transaction->rollBack();
  $state->resetCache();
  if ($old_key === NULL) { $state->delete('ksa_newsletter.api_hash'); } else { $state->set('ksa_newsletter.api_hash', $old_key); }
}
