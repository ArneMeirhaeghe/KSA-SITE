<?php

namespace Drupal\ksa_newsletter;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Xss;

/** Fixed, portable HTML subset for the future newsletter assembly app. */
final class NewsletterText {
  public const TAGS = ['p', 'br', 'strong', 'em', 'h2', 'ul', 'ol', 'li', 'a'];

  /** Use Drupal's configured filters for both the detail page and the API. */
  public static function render(\Drupal\Core\Entity\FieldableEntityInterface $article): string {
    if ($article->hasField('content') && !$article->get('content')->isEmpty()) {
      $item = $article->get('content')->first();
      $build = ['#type' => 'processed_text', '#text' => $item->value, '#format' => $item->format];
      return (string) \Drupal::service('renderer')->renderInIsolation($build);
    }
    $body = (string) $article->get('body')->value;
    return self::clean($body, (bool) preg_match('/<(?:p|br|strong|em|h2|ul|ol|li|a)\b/i', $body));
  }

  public static function clean(string $text, bool $html): string {
    if (!$html) {
      return nl2br(Html::escape($text));
    }
    $document = Html::load(Xss::filter($text, [...self::TAGS, 'b', 'i']));
    foreach (iterator_to_array($document->getElementsByTagName('*')) as $element) {
      $href = $element->tagName === 'a' ? trim($element->getAttribute('href')) : '';
      while ($element->attributes->length) {
        $element->removeAttributeNode($element->attributes->item(0));
      }
      if ($element->tagName === 'a') {
        // Only explicit web/mail URLs, site paths and fragment links survive.
        if ($href !== '' && !preg_match('/[\x00-\x20\x7f]/', $href) && preg_match('~^(?:https?://|mailto:|/(?!/)|#)~i', $href)) {
          $element->setAttribute('href', $href);
        }
        else {
          while ($element->firstChild) { $element->parentNode->insertBefore($element->firstChild, $element); }
          $element->parentNode->removeChild($element);
        }
      }
      if (in_array($element->tagName, ['b', 'i'], TRUE)) {
        $replacement = $document->createElement($element->tagName === 'b' ? 'strong' : 'em');
        while ($element->firstChild) { $replacement->appendChild($element->firstChild); }
        $element->parentNode->replaceChild($replacement, $element);
      }
    }
    return Html::serialize($document);
  }
}
