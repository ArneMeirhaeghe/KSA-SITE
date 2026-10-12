<?php

namespace Drupal\ksa_editor\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;

/** Admin-only, escaped before/after audit overview. */
final class LeadershipLogController extends ControllerBase {

  public function overview(Request $request): array {
    $build = ['#cache' => ['max-age' => 0]];
    $build['intro'] = ['#markup' => '<p>Opgeslagen wijzigingen door gebruikers met de rol Leiding. Per veld zie je de volledige oude en nieuwe waarde. De registratie begint bij het inschakelen van dit logboek; oudere wijzigingen worden niet achteraf ingevuld.</p>'];
    if (!$this->moduleHandler()->moduleExists('entity_log')) {
      $build['inactive'] = ['#plain_text' => 'Activeer Entity Log om wijzigingen te registreren.'];
      return $build;
    }
    $build['filter'] = $this->formBuilder()->getForm('Drupal\ksa_editor\Form\LeadershipLogFilter');
    $storage = $this->entityTypeManager()->getStorage('entity_log');
    $query = $storage->getQuery()->accessCheck(FALSE)->condition('log_type', 'ksa_leiding');
    if ($uid = $request->query->get('gebruiker')) {
      $query->condition('user_id', (int) $uid);
    }
    if (in_array($type = $request->query->get('type'), ['node', 'block_content', 'media', 'file', 'user'], TRUE)) {
      $query->condition('entity_logged_id.target_type', $type);
    }
    if ($term = trim((string) $request->query->get('zoek', ''))) {
      $query->condition('name', $term, 'CONTAINS');
    }
    foreach (['vanaf' => '>=', 'tot' => '<'] as $parameter => $operator) {
      $date = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $request->query->get($parameter, ''), new \DateTimeZone('Europe/Brussels'));
      if ($date && $date->format('Y-m-d') === $request->query->get($parameter)) {
        $query->condition('created', ($parameter === 'tot' ? $date->modify('+1 day') : $date)->getTimestamp(), $operator);
      }
    }
    $total = (clone $query)->count()->execute();
    $build['count'] = ['#type' => 'html_tag', '#tag' => 'p', '#value' => $this->t('@count veldwijzigingen · nieuwste eerst · tijdzone Brussel', ['@count' => $total]), '#attributes' => ['class' => ['ksa-log-count']]];
    $ids = $query->sort('id', 'DESC')->pager(25)->execute();
    $build['entries'] = ['#type' => 'container', '#attributes' => ['class' => ['ksa-log-entries']]];
    foreach ($storage->loadMultiple($ids) as $log) {
      $old = json_decode($log->getOldValue(), TRUE);
      $new = json_decode($log->getNewValue(), TRUE);
      $context = $new['context'] ?? [];
      $operation = ['insert' => 'Aangemaakt', 'update' => 'Aangepast', 'delete' => 'Verwijderd'][$context['operation'] ?? ''] ?? 'Aangepast';
      $types = ['node' => 'Inhoud', 'block_content' => 'Blok', 'media' => 'Media', 'file' => 'Bestand', 'user' => 'Profiel'];
      $title = ($context['title'] ?? '') . ' (' . ($types[$context['entity_type'] ?? ''] ?? 'Onderdeel') . ' #' . ($context['id'] ?? '') . ')';
      if (!empty($context['page'])) {
        $title .= ' · Pagina: ' . $context['page']['title'] . ' (#' . $context['page']['id'] . ')';
      }
      $build['entries'][$log->id()] = [
        '#type' => 'inline_template',
        '#template' => '<details class="ksa-log-entry"><summary><span class="ksa-log-entry__time">{{ time }}<small>{{ actor }}</small></span><span class="ksa-log-entry__subject"><strong>{{ field }}</strong><small>{{ title }}</small></span><span class="ksa-log-entry__action">{{ operation }}</span><span class="ksa-log-entry__expand">Voor / na</span></summary><div class="ksa-log-comparison"><section><h3>Voor</h3>{{ before }}</section><section><h3>Na</h3>{{ after }}</section></div><p class="ksa-log-entry__meta">Log #{{ id }} · {{ machine }} · revisie {{ revision }}</p></details>',
        '#context' => [
          'time' => \Drupal::service('date.formatter')->format($log->getCreatedTime(), 'custom', 'd/m/Y H:i:s', 'Europe/Brussels'),
          'actor' => ($context['actor'] ?? 'Onbekend') . ' (#' . $log->getOwnerId() . ')',
          'title' => $title, 'field' => $context['field_label'] ?? '', 'operation' => $operation,
          'before' => self::cell($old['values'] ?? []), 'after' => self::cell($new['values'] ?? []),
          'id' => $log->id(), 'machine' => $context['field'] ?? '', 'revision' => $context['revision'] ?? '—',
        ],
      ];
    }
    if (!$ids) {
      $build['entries']['empty'] = ['#markup' => '<p class="ksa-log-empty">Geen wijzigingen gevonden. Pas de filters aan, of voer als leiding een wijziging uit en sla die op.</p>'];
    }
    $build['pager'] = ['#type' => 'pager'];
    $build['#attached']['library'][] = 'ksa_editor/audit';
    return $build;
  }

  private static function cell(array $values): array {
    $parts = [];
    $files = [];
    foreach ($values as $value) {
      if (array_key_exists('value', $value)) {
        $text = (string) $value['value'];
        if (isset($value['format'])) {
          // Preserve exact HTML as escaped text, never execute historic markup.
          $text .= "\nTekstformaat: " . $value['format'];
        }
        if (!empty($value['summary'])) {
          $text .= "\nSamenvatting: " . $value['summary'];
        }
      }
      elseif (isset($value['naam'])) {
        $text = $value['naam'] . ' (#' . $value['target_id'] . ')';
        foreach ($value['bestanden'] ?? [$value] as $file) {
          if (isset($file['bestand'])) {
            $text .= "\nBestand: " . $file['bestand'];
            $text .= "\nLocatie: " . $file['locatie'];
            $files[] = $file;
          }
        }
        foreach (['alt' => 'Alt-tekst', 'title' => 'Titel'] as $key => $label) {
          if (isset($value[$key])) {
            $text .= "\n$label: " . $value[$key];
          }
        }
      }
      else {
        $text = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
      }
      $parts[] = $text;
    }
    $build['text'] = ['#type' => 'html_tag', '#tag' => 'pre', 'text' => ['#plain_text' => $parts ? implode("\n\n", $parts) : '— (leeg)']];
    foreach ($files as $i => $file) {
      if (str_starts_with($file['locatie'], 'public://')) {
        $build['file_' . $i] = ['#type' => 'link', '#title' => 'Open ' . $file['bestand'], '#url' => \Drupal::service('file_url_generator')->generate($file['locatie']), '#attributes' => ['class' => ['ksa-log-file'], 'target' => '_blank', 'rel' => 'noopener']];
      }
    }
    return $build;
  }

}
