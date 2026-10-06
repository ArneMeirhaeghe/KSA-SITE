<?php

declare(strict_types=1);

namespace Drupal\ksa_agenda;

/**
 * A public Google Calendar ID, normalized from whatever a person pastes.
 *
 * Accepts a raw ID, an embed URL, iframe code, a public .ics address or a
 * "?cid=" share link. Only the ID is stored; every URL is derived from it.
 *
 * Secret ("private-") iCal addresses are rejected on purpose: they work like
 * a password and must never end up in config.
 */
final class CalendarId {

  public const ERROR_EMPTY = 1;
  public const ERROR_SECRET = 2;
  public const ERROR_UNKNOWN = 3;

  private const BASE = 'https://calendar.google.com/calendar';

  /**
   * Matches an e-mail style ID, e.g. "abc@group.calendar.google.com".
   */
  private const ID_PATTERN = '/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/';

  private function __construct(public readonly string $id) {}

  /**
   * Builds a calendar ID from pasted input.
   *
   * @throws \InvalidArgumentException
   *   With one of the ERROR_* constants as code.
   */
  public static function fromInput(string $input): self {
    $input = trim($input);
    if ($input === '') {
      throw new \InvalidArgumentException('No calendar given.', self::ERROR_EMPTY);
    }
    if (str_contains($input, '/private-')) {
      throw new \InvalidArgumentException('Secret iCal address given.', self::ERROR_SECRET);
    }

    $candidate = self::extract($input);
    if ($candidate === NULL || !preg_match(self::ID_PATTERN, $candidate)) {
      throw new \InvalidArgumentException('Not a Google Calendar ID or link.', self::ERROR_UNKNOWN);
    }
    return new self($candidate);
  }

  /**
   * The public iCal feed the site reads.
   */
  public function feedUrl(): string {
    return self::BASE . '/ical/' . rawurlencode($this->id) . '/public/basic.ics';
  }

  /**
   * The Google month view for an iframe.
   *
   * @param array<string, string> $options
   *   Extra query parameters, e.g. ['showTitle' => '0'].
   */
  public function embedUrl(array $options = []): string {
    $query = ['src' => $this->id, 'ctz' => 'Europe/Brussels'] + $options;
    return self::BASE . '/embed?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
  }

  /**
   * The link that adds this calendar to a visitor's own Google Calendar.
   */
  public function subscribeUrl(): string {
    return self::BASE . '/u/0?cid=' . rtrim(base64_encode($this->id), '=');
  }

  /**
   * The same public feed as a webcal link, for Apple Calendar and Outlook.
   */
  public function webcalUrl(): string {
    return 'webcal://' . substr($this->feedUrl(), strlen('https://'));
  }

  /**
   * Pulls the bare ID out of a link, iframe code or raw value.
   */
  private static function extract(string $input): ?string {
    // Iframe code: keep only the src attribute.
    if (preg_match('/<iframe[^>]*\ssrc="([^"]+)"/i', $input, $m)) {
      $input = html_entity_decode($m[1]);
    }

    if (!preg_match('#^https?://#i', $input)) {
      return rawurldecode($input);
    }

    $host = strtolower((string) parse_url($input, PHP_URL_HOST));
    if ($host !== 'calendar.google.com') {
      return NULL;
    }

    // Embed URL: ...?src=<id>. Use the first src when several are present.
    if (preg_match('/[?&]src=([^&#]+)/', $input, $m)) {
      return rawurldecode($m[1]);
    }

    // Share link: ...?cid=<base64 of the id>.
    if (preg_match('/[?&]cid=([^&#]+)/', $input, $m)) {
      $decoded = base64_decode(strtr(rawurldecode($m[1]), '-_', '+/'), TRUE);
      return $decoded === FALSE ? NULL : $decoded;
    }

    // Public feed: /calendar/ical/<id>/public/basic.ics.
    if (preg_match('#/ical/([^/]+)/public/#', $input, $m)) {
      return rawurldecode($m[1]);
    }

    return NULL;
  }

}
