<?php

namespace Drupal\ksa_newsletter\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\ksa_newsletter\NewsletterAccess;

/** A private contribution, deliberately without publication/workflow status. */
#[ContentEntityType(
  id: 'newsletter_article',
  label: new TranslatableMarkup('Nieuwsbriefartikel'),
  label_collection: new TranslatableMarkup('Nieuwsbriefartikelen'),
  handlers: ['access' => NewsletterAccess::class],
  base_table: 'ksa_newsletter_article',
  entity_keys: ['id' => 'id', 'uuid' => 'uuid', 'label' => 'title'],
  admin_permission: 'administer newsletter articles',
  links: ['canonical' => '/admin/content/nieuwsbrief/{newsletter_article}'],
  internal: TRUE,
)]
final class NewsletterArticle extends ContentEntityBase {
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields['title'] = BaseFieldDefinition::create('string')->setLabel('Titel')->setRequired(TRUE)->setSetting('max_length', 200);
    $fields['writer'] = BaseFieldDefinition::create('string')->setLabel('Schrijver')->setRequired(TRUE)->setSetting('max_length', 120);
    // Fixed sanitized HTML subset: p, br, strong and em; no attributes.
    $fields['body'] = BaseFieldDefinition::create('string_long')->setLabel('Tekst')->setRequired(TRUE);
    $fields['activity_date'] = BaseFieldDefinition::create('datetime')->setLabel('Activiteitsdatum')->setSetting('datetime_type', 'date');
    $fields['created'] = BaseFieldDefinition::create('created')->setLabel('Ingestuurd op');
    $fields['uid'] = BaseFieldDefinition::create('entity_reference')->setLabel('Ingestuurd door')->setSetting('target_type', 'user');
    $fields['photos'] = BaseFieldDefinition::create('entity_reference')->setLabel('Foto’s')->setSetting('target_type', 'media')->setSetting('handler_settings', ['target_bundles' => ['newsletter_photo' => 'newsletter_photo']])->setCardinality(5);
    return $fields;
  }
}
