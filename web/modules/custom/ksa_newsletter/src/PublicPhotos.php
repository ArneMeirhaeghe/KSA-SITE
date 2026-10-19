<?php
namespace Drupal\ksa_newsletter;

use Drupal\Core\File\FileSystemInterface;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\media\MediaInterface;

/** Explicitly selected, immutable copies; originals remain private. */
final class PublicPhotos {
  public static function get(MediaInterface $source): ?array {
    $record = \Drupal::keyValue('ksa_newsletter.public_photos')->get($source->uuid());
    $file = $source->get('field_newsletter_image')->entity;
    if (!$record || !$file || $record['source_file_uuid'] !== $file->uuid() || !is_file($record['uri'])) { return NULL; }
    return ['id' => $record['uuid'], 'url' => \Drupal::service('file_url_generator')->generateAbsoluteString($record['uri']), 'width' => $record['width'], 'height' => $record['height'], 'mime_type' => $record['mime_type']];
  }

  public static function publish(MediaInterface $source, bool $new_version = FALSE): array {
    if ($source->bundle() !== 'newsletter_photo') { throw new \InvalidArgumentException('Not a newsletter photo.'); }
    $lock = \Drupal::lock();
    $lock_name = 'ksa_newsletter.publish.' . $source->uuid();
    if (!$lock->acquire($lock_name, 120)) { throw new \RuntimeException('Publication busy.'); }
    $uri = NULL; $file = NULL; $media = NULL;
    try {
      if (!$new_version && $existing = self::get($source)) { return $existing; }
      $original = $source->get('field_newsletter_image')->entity;
      if (!$original || !str_starts_with($original->getFileUri(), 'private://newsletter/')) { throw new \RuntimeException('Invalid source.'); }
      $directory = 'public://newsletter';
      if (!\Drupal::service('file_system')->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS)) { throw new \RuntimeException('Public directory unavailable.'); }
      $image = \Drupal::service('image.factory')->get($original->getFileUri());
      if (!$image->isValid() || !$image->scale((int) Settings::get('photo_max_width'), (int) Settings::get('photo_max_height'), FALSE)) { throw new \RuntimeException('Image scaling failed.'); }
      $extension = $image->getMimeType() === 'image/jpeg' ? 'jpg' : 'png';
      if ($image->getMimeType() === 'image/webp' && !$image->convert('png')) { throw new \RuntimeException('Image conversion failed.'); }
      $uuid = \Drupal::service('uuid')->generate();
      $uri = $directory . '/' . $uuid . '.' . $extension;
      if (file_exists($uri) || !$image->save($uri)) { throw new \RuntimeException('Image save failed.'); }
      $file = File::create(['uri' => $uri, 'filename' => $uuid . '.' . $extension, 'status' => 1, 'uid' => \Drupal::currentUser()->id()]);
      $file->save();
      // Reuse the site's normal image Media type; private originals use their own type.
      $media = Media::create(['bundle' => 'image', 'name' => 'Nieuwsbrief — ' . $source->label(), 'status' => 1, 'uid' => \Drupal::currentUser()->id(), 'field_media_image' => ['target_id' => $file->id(), 'alt' => $source->get('field_newsletter_image')->alt ?: $source->label()]]);
      $media->save();
      \Drupal::keyValue('ksa_newsletter.public_photos')->set($source->uuid(), ['source_file_uuid' => $original->uuid(), 'uuid' => $uuid, 'uri' => $uri, 'file_id' => $file->id(), 'media_id' => $media->id(), 'width' => $image->getWidth(), 'height' => $image->getHeight(), 'mime_type' => $image->getMimeType()]);
      return self::get($source);
    }
    catch (\Throwable $exception) {
      if ($media) { $media->delete(); }
      if ($file) { $file->delete(); }
      elseif ($uri && file_exists($uri)) { \Drupal::service('file_system')->delete($uri); }
      throw $exception;
    }
    finally { $lock->release($lock_name); }
  }
}
