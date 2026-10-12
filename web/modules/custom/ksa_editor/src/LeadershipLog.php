<?php

namespace Drupal\ksa_editor;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;

/** Detailed field snapshots, stored in the contributed Entity Log storage. */
final class LeadershipLog {

  public static function record(EntityInterface $entity, string $operation): void {
    $actor = \Drupal::currentUser();
    if (!$entity instanceof ContentEntityInterface
      || !in_array($entity->getEntityTypeId(), ['node', 'block_content', 'media', 'file', 'user'], TRUE)
      || !in_array('content_editor', $actor->getRoles(), TRUE)
      || !\Drupal::moduleHandler()->moduleExists('entity_log')) {
      return;
    }
    $original = $operation === 'update' ? $entity->getOriginal() : NULL;
    $context = [
      'operation' => $operation,
      'actor' => $actor->getAccountName(),
      'entity_type' => $entity->getEntityTypeId(),
      'bundle' => $entity->bundle(),
      'id' => $entity->id(),
      'title' => (string) $entity->label(),
      'revision' => $entity->getEntityType()->isRevisionable() ? $entity->getRevisionId() : NULL,
      'language' => $entity->language()->getId(),
    ];
    // Inline blocks are saved while their page is saved. Retain that context.
    $parent = \Drupal::routeMatch()->getParameter('node');
    if ($parent instanceof EntityInterface) {
      $context['page'] = ['id' => $parent->id(), 'title' => $parent->label()];
    }
    foreach ($entity->getFieldDefinitions() as $name => $definition) {
      if ($definition->isComputed() || $definition->isReadOnly()) {
        continue;
      }
      // Never record authentication data, technical timestamps or serialized PHP.
      if (in_array($name, ['uid', 'revision_uid', 'revision_user', 'revision_created', 'revision_timestamp', 'revision_default', 'revision_translation_affected', 'revision_log', 'revision_log_message', 'changed', 'created', 'default_langcode', 'langcode', 'uuid', 'reusable'], TRUE)
        || preg_match('/pass|token|secret|api_key|calendar_id/i', $name)) {
        continue;
      }
      if ($entity->getEntityTypeId() === 'user' && !in_array($name, ['name', 'mail', 'user_picture', 'timezone', 'preferred_langcode'], TRUE)) {
        continue;
      }
      $before = $operation === 'insert' ? [] : self::value($original ?? $entity, $name);
      $after = $operation === 'delete' ? [] : self::value($entity, $name);
      if ($before == $after) {
        continue;
      }
      $metadata = $context + ['field' => $name, 'field_label' => (string) $definition->getLabel()];
      \Drupal::entityTypeManager()->getStorage('entity_log')->create([
        'name' => mb_substr($context['title'] . ' — ' . $definition->getLabel(), 0, 255),
        'label' => mb_substr((string) $definition->getLabel(), 0, 255),
        'log_type' => 'ksa_leiding',
        'user_id' => $actor->id(),
        'hostname' => 'niet geregistreerd',
        'entity_logged_id' => ['target_type' => $entity->getEntityTypeId(), 'target_id' => $entity->id()],
        'old_value' => json_encode(['context' => $metadata, 'values' => $before], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
        'new_value' => json_encode(['context' => $metadata, 'values' => $after], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
      ])->save();
    }
  }

  /** Keep ordered values, including media/file names at the time of the change. */
  public static function value(ContentEntityInterface $entity, string $name): array {
    $field = $entity->get($name);
    if ($field->isEmpty()) {
      return [];
    }
    if ($field->getFieldDefinition()->getType() === 'layout_section') {
      $sections = [];
      foreach ($field as $item) {
        $components = [];
        foreach ($item->section->getComponents() as $component) {
          $config = $component->getPlugin()->getConfiguration();
          $components[] = ['block' => $component->getPluginId(), 'region' => $component->getRegion(), 'weight' => $component->getWeight(), 'revision' => $config['block_revision_id'] ?? NULL];
        }
        $sections[] = ['layout' => $item->section->getLayoutId(), 'blocks' => $components];
      }
      return $sections;
    }
    $values = $field->getValue();
    $target_type = $field->getFieldDefinition()->getSetting('target_type');
    if ($target_type) {
      foreach ($values as &$value) {
        if (empty($value['target_id'])) {
          continue;
        }
        $reference = \Drupal::entityTypeManager()->getStorage($target_type)->load($value['target_id']);
        $value['naam'] = $reference ? (string) $reference->label() : '(verwijderd)';
        if ($reference instanceof \Drupal\media\MediaInterface) {
          $source_field = $reference->getSource()->getConfiguration()['source_field'] ?? NULL;
          if ($source_field && $reference->hasField($source_field)) {
            $value['bestanden'] = self::value($reference, $source_field);
          }
        }
        elseif ($reference instanceof \Drupal\file\FileInterface) {
          $value['bestand'] = $reference->getFilename();
          $value['locatie'] = $reference->getFileUri();
        }
      }
    }
    return $values;
  }

}
