<?php

namespace Drupal\ksa_newsletter\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\RevisionLogEntityTrait;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\ksa_newsletter\NewsletterAccess;

/** Private, fieldable contributions with Drupal's standard revision support. */
#[ContentEntityType(
  id: 'newsletter_article',
  label: new TranslatableMarkup('Nieuwsbriefartikel'),
  label_collection: new TranslatableMarkup('Nieuwsbriefartikelen'),
  handlers: [
    'access' => NewsletterAccess::class,
    'view_builder' => 'Drupal\Core\Entity\EntityViewBuilder',
    'list_builder' => 'Drupal\Core\Entity\EntityListBuilder',
    'views_data' => 'Drupal\views\EntityViewsData',
    'form' => [
      'default' => 'Drupal\ksa_newsletter\Form\ArticleForm',
      'edit' => 'Drupal\ksa_newsletter\Form\ArticleForm',
      'delete' => 'Drupal\Core\Entity\ContentEntityDeleteForm',
      'revision-revert' => 'Drupal\Core\Entity\Form\RevisionRevertForm',
    ],
    'route_provider' => ['revision' => 'Drupal\Core\Entity\Routing\RevisionHtmlRouteProvider'],
  ],
  base_table: 'ksa_newsletter_article',
  revision_table: 'ksa_newsletter_article_revision',
  entity_keys: ['id' => 'id', 'uuid' => 'uuid', 'label' => 'title', 'revision' => 'revision_id'],
  revision_metadata_keys: ['revision_user' => 'revision_user', 'revision_created' => 'revision_created', 'revision_log_message' => 'revision_log_message'],
  admin_permission: 'administer newsletter articles',
  field_ui_base_route: 'ksa_newsletter.configuration',
  show_revision_ui: TRUE,
  internal: TRUE,
  links: [
    'canonical' => '/admin/content/nieuwsbrief/{newsletter_article}',
    'collection' => '/admin/content/nieuwsbrief',
    'edit-form' => '/admin/content/nieuwsbrief/{newsletter_article}/bewerken',
    'delete-form' => '/admin/content/nieuwsbrief/{newsletter_article}/verwijderen',
    'version-history' => '/admin/content/nieuwsbrief/{newsletter_article}/revisions',
    'revision' => '/admin/content/nieuwsbrief/{newsletter_article}/revisions/{newsletter_article_revision}/view',
    'revision-revert-form' => '/admin/content/nieuwsbrief/{newsletter_article}/revisions/{newsletter_article_revision}/revert',
  ],
)]
final class NewsletterArticle extends ContentEntityBase implements RevisionLogInterface {
  use RevisionLogEntityTrait;

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::revisionLogBaseFieldDefinitions($entity_type);
    $fields['title'] = BaseFieldDefinition::create('string')->setLabel('Titel')->setRequired(TRUE)->setSetting('max_length', 200);
    $fields['writer'] = BaseFieldDefinition::create('string')->setLabel('Schrijver')->setRequired(TRUE)->setSetting('max_length', 120);
    // Retain the old storage for a lossless migration and legacy integrations.
    $fields['body'] = BaseFieldDefinition::create('string_long')->setLabel('Oorspronkelijke tekst')->setDisplayConfigurable('form', FALSE)->setDisplayConfigurable('view', FALSE);
    $fields['content'] = BaseFieldDefinition::create('text_long')->setLabel('Tekst')->setRequired(TRUE)
      ->setDisplayOptions('form', ['type' => 'text_textarea', 'weight' => 2, 'settings' => ['rows' => 12]])
      ->setDisplayOptions('view', ['type' => 'text_default', 'label' => 'hidden', 'weight' => 2]);
    $fields['activity_date'] = BaseFieldDefinition::create('datetime')->setLabel('Activiteitsdatum')->setSetting('datetime_type', 'date');
    $fields['created'] = BaseFieldDefinition::create('created')->setLabel('Ingestuurd op');
    $fields['uid'] = BaseFieldDefinition::create('entity_reference')->setLabel('Ingestuurd door')->setSetting('target_type', 'user');
    $fields['photos'] = BaseFieldDefinition::create('entity_reference')->setLabel('Foto’s')->setSetting('target_type', 'media')->setSetting('handler_settings', ['target_bundles' => ['newsletter_photo' => 'newsletter_photo']])->setCardinality(5);
    $widgets = ['title' => 'string_textfield', 'writer' => 'string_textfield', 'activity_date' => 'datetime_default', 'created' => 'datetime_timestamp', 'uid' => 'entity_reference_autocomplete', 'photos' => 'media_library_widget'];
    $formatters = ['title' => 'string', 'writer' => 'string', 'activity_date' => 'datetime_default', 'created' => 'timestamp', 'uid' => 'entity_reference_label', 'photos' => 'entity_reference_entity_view'];
    $weight = 0;
    foreach ($widgets as $name => $widget) {
      $fields[$name]->setDisplayOptions('form', ['type' => $widget, 'weight' => $weight])
        ->setDisplayOptions('view', ['type' => $formatters[$name], 'label' => 'above', 'weight' => $weight]);
      $weight += 2;
    }
    foreach (['title', 'writer', 'content', 'activity_date', 'created', 'uid', 'photos'] as $name) {
      $fields[$name]->setRevisionable(TRUE)->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);
    }
    $fields['body']->setRevisionable(TRUE);
    return $fields;
  }

  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    if ($this->isNewRevision()) {
      $this->setRevisionCreationTime(\Drupal::time()->getRequestTime());
      $this->setRevisionUserId(\Drupal::currentUser()->id());
    }
  }
}
