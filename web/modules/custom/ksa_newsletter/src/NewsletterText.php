<?php

namespace Drupal\ksa_newsletter;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Xss;

/** Fixed, portable HTML subset for the future newsletter assembly app. */
final class NewsletterText {
  public static function clean(string $text, bool $html): string {
    if (!$html) {
      return nl2br(Html::escape($text));
    }
    $document = Html::load(Xss::filter($text, ['p', 'br', 'strong', 'em', 'b', 'i']));
    foreach (iterator_to_array($document->getElementsByTagName('*')) as $element) {
      while ($element->attributes->length) {
        $element->removeAttributeNode($element->attributes->item(0));
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
