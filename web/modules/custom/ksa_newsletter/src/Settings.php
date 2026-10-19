<?php
namespace Drupal\ksa_newsletter;

/** Defaults also protect the short interval before importing new configuration. */
final class Settings {
  public static function get(string $key): mixed {
    $defaults = ['text_format' => 'newsletter', 'photo_max_width' => 1600, 'photo_max_height' => 1600, 'api_requests_per_minute' => 120, 'submissions_per_hour' => 10, 'submissions_per_day' => 25, 'submissions_total_per_day' => 100];
    return \Drupal::config('ksa_newsletter.settings')->get($key) ?? $defaults[$key];
  }
}
