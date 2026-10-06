<?php

declare(strict_types=1);

namespace Drupal\ksa_agenda\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Component\Utility\Html;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\ksa_agenda\AgendaFetcher;
use Drupal\ksa_agenda\DateLabel;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Upcoming events from Google, with a pager, and the month view beside them.
 */
#[Block(
  id: 'ksa_agenda',
  admin_label: new TranslatableMarkup('Agenda uit Google'),
  category: new TranslatableMarkup('KSA'),
)]
final class AgendaBlock extends BlockBase implements ContainerFactoryPluginInterface {

  private const PER_PAGE = 5;

  /**
   * Pager element; Rondes has no other pager.
   */
  private const PAGER_ELEMENT = 0;

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly AgendaFetcher $fetcher,
    private readonly PagerManagerInterface $pagerManager,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('ksa_agenda.fetcher'),
      $container->get('pager.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    // The component prints the heading itself, next to "Abonneren".
    return ['label' => 'Data en planning', 'label_display' => '0'];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state): array {
    // Layout Builder only shows the title here: say where the content lives.
    $form['ksa_agenda_help'] = [
      '#theme' => 'status_messages',
      '#message_list' => ['status' => [
        $this->t('<strong>De activiteiten komen uit Google Agenda.</strong> Toevoegen, wijzigen of verwijderen doe je daar, niet hier. <a href=":url">Welke agenda de site toont</a>', [':url' => Url::fromRoute('ksa_agenda.settings')->toString()]),
      ]],
      '#status_headings' => ['status' => $this->t('Uitleg')],
      '#weight' => -100,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $build = [
      '#cache' => [
        'tags' => [AgendaFetcher::CACHE_TAG, 'config:ksa_agenda.settings'],
        'contexts' => ['url.query_args.pagers:' . self::PAGER_ELEMENT],
      ],
    ];

    $calendar = $this->fetcher->calendar();
    if ($calendar === NULL) {
      return $build;
    }

    $events = $this->fetcher->upcoming();
    $pager = $this->pagerManager->createPager(count($events), self::PER_PAGE, self::PAGER_ELEMENT);
    $page = array_slice($events, $pager->getCurrentPage() * self::PER_PAGE, self::PER_PAGE);

    $cards = [];
    $cacheability = CacheableMetadata::createFromRenderArray($build);
    foreach ($page as $event) {
      // The card links to an .ics with only this event.
      $url = Url::fromRoute('ksa_agenda.event_ics', ['event' => $event['id']])->toString(TRUE);
      $cacheability->addCacheableDependency($url);
      $slots = [
        'eyebrow' => ['#plain_text' => DateLabel::format($event)],
        'title' => ['#markup' => Html::escape($event['title']) . '<span class="visually-hidden">, ' . $this->t('zet in je agenda') . '</span>'],
      ];
      if ($event['location'] !== '') {
        $slots['meta'] = ['#plain_text' => $event['location']];
      }
      $cards[] = [
        '#type' => 'component',
        '#component' => 'ksa:card',
        '#props' => [
          'variant' => 'event',
          'heading_level' => 4,
          'url' => $url->getGeneratedUrl(),
          'more_label' => (string) $this->t('In je agenda'),
        ],
        '#slots' => $slots,
      ];
    }
    if ($cards === []) {
      $cards = ['#markup' => '<p class="agenda__empty">' . $this->t('Er staan nog geen activiteiten gepland.') . '</p>'];
    }

    $build += [
      '#type' => 'component',
      '#component' => 'ksa:agenda',
      '#props' => [
        'heading' => (string) $this->label(),
        'subscribe_google' => $calendar->subscribeUrl(),
        'subscribe_ical' => $calendar->webcalUrl(),
        'calendar_url' => $calendar->embedUrl([
          'mode' => 'MONTH',
          'color' => '#287d27',
          'wkst' => '2',
          'hl' => 'nl',
          'showTitle' => '0',
          'showPrint' => '0',
          'showTabs' => '0',
          'showCalendars' => '0',
          'showTz' => '0',
        ]),
      ],
      '#slots' => [
        'events' => $cards,
      ],
    ];
    $build['#props'] += $this->pagerProps($pager->getCurrentPage(), $pager->getTotalPages(), $cacheability);
    $cacheability->applyTo($build);
    return $build;
  }

  /**
   * Props for the agenda pager (Figma: "Prev 1 2 3 Next", mobile 121:1947).
   *
   * Shows at most three page numbers around the current page, so the pager
   * fits on one row in the narrow list column. Links end in #agenda, so
   * visitors stay at the agenda after a click.
   */
  private function pagerProps(int $current, int $total, CacheableMetadata $cacheability): array {
    $link = function (int $index) use ($cacheability): string {
      $url = Url::fromRoute('<current>', [], [
        'query' => $this->pagerManager->getUpdatedParameters([], self::PAGER_ELEMENT, $index),
        'fragment' => 'agenda',
      ])->toString(TRUE);
      $cacheability->addCacheableDependency($url);
      return $url->getGeneratedUrl();
    };

    $first = max(0, min($current - 1, $total - 3));
    $numbers = [];
    for ($i = $first; $i < min($total, $first + 3); $i++) {
      $numbers[] = ['number' => $i + 1, 'url' => $link($i), 'current' => $i === $current];
    }

    return [
      'page' => $current + 1,
      'pages' => $total,
      'page_links' => $numbers,
      'previous_url' => $current > 0 ? $link($current - 1) : '',
      'next_url' => $current < $total - 1 ? $link($current + 1) : '',
    ];
  }
}
