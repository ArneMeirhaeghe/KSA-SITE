<?php

declare(strict_types=1);

namespace Drupal\ksa_agenda\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\ksa_agenda\AgendaFetcher;
use Drupal\ksa_agenda\CalendarId;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Lets the leiding choose which public Google Calendar the site shows.
 */
final class SettingsForm extends ConfigFormBase {

  private AgendaFetcher $fetcher;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    $form = parent::create($container);
    $form->fetcher = $container->get('ksa_agenda.fetcher');
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ksa_agenda_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['ksa_agenda.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $current = (string) $this->config('ksa_agenda.settings')->get('calendar_id');

    // Tell the leiding first where the activities really live. A real
    // status message, so the admin theme gives it readable colours.
    $form['help'] = [
      '#theme' => 'status_messages',
      '#message_list' => ['status' => [
        $this->t('<strong>De activiteiten beheer je in Google Agenda, niet hier.</strong> Toevoegen, wijzigen of verwijderen doe je daar. De site neemt dat vanzelf over. <a href=":url" target="_blank" rel="noopener">Open Google Agenda</a>', [':url' => 'https://calendar.google.com/calendar/r']),
        $this->t('Hier kies je alleen welke Google Agenda de site toont.'),
        $this->t('De site haalt wijzigingen op bij cron, normaal elke 3 uur. Dat vereist dat cron regelmatig draait. Meteen vernieuwen? Klik op "Instellingen opslaan".'),
      ]],
      '#status_headings' => ['status' => $this->t('Uitleg')],
    ];

    $form['calendar'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Google Agenda'),
      '#description' => $this->t('Plak de agenda-ID, de openbare link of de insluitcode. Je vindt ze in Google Agenda, bij Instellingen, onder "Agenda integreren". De agenda moet openbaar zijn. Leeg laten = geen agenda op de site.'),
      '#default_value' => $current,
      '#maxlength' => 2048,
    ];

    $calendar = NULL;
    try {
      $calendar = CalendarId::fromInput($current);
    }
    catch (\InvalidArgumentException) {
      // Empty or broken value in config: show the field only.
    }

    if ($calendar) {
      $status = $this->fetcher->status();
      $fetched = $status['fetched']
        ? (new \DateTimeImmutable('@' . $status['fetched']))->setTimezone(new \DateTimeZone('Europe/Brussels'))->format('d/m/Y H:i')
        : (string) $this->t('Nog niet gesynchroniseerd');
      $form['sync_status'] = [
        '#type' => 'item',
        '#title' => $this->t('Laatste geslaagde synchronisatie'),
        '#plain_text' => $fetched,
        '#description' => $status['failure'] ? $this->t('De laatste poging is mislukt. De site gebruikt de laatst opgehaalde activiteiten. Controleer of de agenda openbaar is en probeer opnieuw.') : '',
      ];
      $form['links'] = [
        '#theme' => 'item_list',
        '#title' => $this->t('Huidige agenda'),
        '#items' => [
          ['#type' => 'link', '#title' => $this->t('Bekijk in Google Agenda'), '#url' => Url::fromUri($calendar->embedUrl())],
          ['#type' => 'link', '#title' => $this->t('Openbare feed (iCal)'), '#url' => Url::fromUri($calendar->feedUrl())],
        ],
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $input = trim((string) $form_state->getValue('calendar'));
    if ($input === '') {
      $form_state->setValue('calendar', '');
      return;
    }

    try {
      $form_state->setValue('calendar', CalendarId::fromInput($input)->id);
    }
    catch (\InvalidArgumentException $e) {
      $message = match ($e->getCode()) {
        CalendarId::ERROR_SECRET => $this->t('Dit is het geheime adres van de agenda. Dat werkt als een wachtwoord, en hoort niet op de site. Maak de agenda openbaar, en plak de openbare link.'),
        default => $this->t('Dit herkennen we niet als een Google Agenda. Plak de agenda-ID, of een link van calendar.google.com.'),
      };
      $form_state->setErrorByName('calendar', $message);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('ksa_agenda.settings')
      ->set('calendar_id', $form_state->getValue('calendar'))
      ->save();
    parent::submitForm($form, $form_state);

    // Fetch right away, so the site does not wait for the next cron run.
    $count = $this->fetcher->refresh();
    if ($count === NULL) {
      $this->messenger()->addWarning($this->t('De agenda kon niet opgehaald worden. Staat ze op openbaar in Google Agenda?'));
    }
    elseif ($form_state->getValue('calendar') !== '') {
      $this->messenger()->addStatus($this->t('@count komende activiteiten gevonden.', ['@count' => $count]));
    }
  }

}
