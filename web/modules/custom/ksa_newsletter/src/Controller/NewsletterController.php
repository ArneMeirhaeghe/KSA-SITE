<?php
namespace Drupal\ksa_newsletter\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Routing\LocalRedirectResponse;
use Drupal\ksa_newsletter\Entity\NewsletterArticle;
use Drupal\ksa_newsletter\SubmissionGate;
use Drupal\ksa_newsletter\Form\SubmissionForm;
use Symfony\Component\HttpFoundation\Request;

final class NewsletterController extends ControllerBase {
  public function entry(Request $request) {
    if ($request->query->has('toegang')) {
      $token = $request->query->get('toegang', '');
      if (is_string($token)) {
        SubmissionGate::accept($request, $token);
      }
      $response = new LocalRedirectResponse(Url::fromRoute('ksa_newsletter.entry')->toString());
      $response->headers->set('Cache-Control', 'private, no-store');
      $response->headers->set('Referrer-Policy', 'no-referrer');
      return $response;
    }
    if (!SubmissionGate::allowed($request)) {
      return ['#cache' => ['max-age' => 0], '#type' => 'component', '#component' => 'ksa:page-intro', '#props' => ['variant' => 'page'], '#slots' => ['text' => ['#plain_text' => 'Om een bijdrage in te sturen heb je de instuurlink nodig. Vraag de actuele link aan de nieuwsbriefbeheerder.']]];
    }
    return [
      '#cache' => ['max-age' => 0],
      'intro' => ['#type' => 'component', '#component' => 'ksa:page-intro', '#props' => ['variant' => 'page'], '#slots' => ['text' => ['#plain_text' => 'Deel je verhaal, aankondiging of terugblik met de KSA. Je bijdrage komt bij de nieuwsbriefbeheerder terecht.']]],
      'received' => $request->getSession()->getFlashBag()->get('ksa_newsletter.received') ? ['#type' => 'component', '#component' => 'ksa:newsletter-received'] : [],
      'compose' => [
        '#type' => 'container', '#attributes' => ['class' => ['newsletter-compose']],
        'form' => $this->formBuilder()->getForm(SubmissionForm::class),
        'fox' => ['#type' => 'container', '#attributes' => ['class' => ['newsletter-compose__fox']], 'mascot' => ['#type' => 'component', '#component' => 'ksa:newsletter-fox', '#props' => ['variant' => 'form']]],
      ],
    ];
  }
  public function overview(Request $request): array {
    $storage = $this->entityTypeManager()->getStorage('newsletter_article');
    $query = $storage->getQuery()->accessCheck(FALSE)->sort('created', 'DESC')->sort('id', 'DESC');
    $search = mb_substr(trim((string) $request->query->get('zoek', '')), 0, 200);
    if ($search !== '') {
      $query->condition($query->orConditionGroup()->condition('title', $search, 'CONTAINS')->condition('writer', $search, 'CONTAINS'));
    }
    foreach (['van' => '>=', 'tot' => '<'] as $key => $operator) {
      $value = (string) $request->query->get($key, '');
      $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value, new \DateTimeZone('Europe/Brussels'));
      if ($date && $date->format('Y-m-d') === $value) {
        $query->condition('created', ($key === 'tot' ? $date->modify('+1 day') : $date)->getTimestamp(), $operator);
      }
    }
    $build = ['#cache' => ['max-age' => 0], '#attached' => ['library' => ['ksa_newsletter/form']]];
    $build['help'] = ['#markup' => '<p>Alle bijdragen staan hier privé. Selecteer een periode op instuurdatum om de artikelen voor een nieuwsbrief te verzamelen.</p>'];
    $build['settings'] = Link::createFromRoute('Instuurlink beheren', 'ksa_newsletter.settings')->toRenderable();
    $build['filter'] = ['#type' => 'inline_template', '#template' => '<form method="get" class="newsletter-admin-filter"><label>Titel of schrijver <input name="zoek" value="{{ zoek }}"></label> <label>Ingestuurd vanaf <input type="date" name="van" value="{{ van }}"></label> <label>Tot en met <input type="date" name="tot" value="{{ tot }}"></label> <button type="submit" class="button">Filteren</button> <a href="{{ reset }}">Wissen</a></form>', '#context' => ['zoek' => $search, 'van' => (string) $request->query->get('van', ''), 'tot' => (string) $request->query->get('tot', ''), 'reset' => Url::fromRoute('ksa_newsletter.overview')->toString()]];
    $rows = [];
    foreach ($storage->loadMultiple($query->pager(25)->execute()) as $article) {
      $rows[] = [['data' => $article->toLink()->toRenderable()], $article->get('writer')->value, \Drupal::service('date.formatter')->format($article->get('created')->value, 'custom', 'd/m/Y H:i', 'Europe/Brussels'), $article->get('activity_date')->value ?: '—', $article->get('photos')->count()];
    }
    $build['table'] = ['#type' => 'table', '#header' => ['Titel', 'Schrijver', 'Ingestuurd op', 'Activiteitsdatum', 'Foto’s'], '#rows' => $rows, '#empty' => 'Nog geen bijdragen in deze periode.'];
    $build['pager'] = ['#type' => 'pager'];
    return $build;
  }
  public function detail(NewsletterArticle $newsletter_article): array {
    $article = $newsletter_article;
    $build = ['#cache' => ['max-age' => 0], '#attached' => ['library' => ['ksa_newsletter/form']]];
    $build['back'] = Link::createFromRoute('← Alle nieuwsbriefartikelen', 'ksa_newsletter.overview')->toRenderable();
    $build['title'] = ['#type' => 'html_tag', '#tag' => 'h2', '#value' => \Drupal\Component\Utility\Html::escape($article->label())];
    $build['info'] = ['#theme' => 'item_list', '#items' => ['Schrijver: ' . $article->get('writer')->value, 'Ingestuurd: ' . \Drupal::service('date.formatter')->format($article->get('created')->value, 'custom', 'd/m/Y H:i', 'Europe/Brussels'), 'Activiteitsdatum: ' . ($article->get('activity_date')->value ?: 'Niet opgegeven')]];
    $build['body'] = ['#type' => 'container', '#attributes' => ['class' => ['newsletter-text']], 'text' => ['#markup' => \Drupal\ksa_newsletter\NewsletterText::clean($article->get('body')->value, (bool) preg_match('/<(?:p|br|strong|em|h2|ul|ol|li|a)\b/i', $article->get('body')->value)), '#allowed_tags' => \Drupal\ksa_newsletter\NewsletterText::TAGS]];
    $build['photos'] = ['#type' => 'container', '#attributes' => ['class' => ['newsletter-photos']]];
    foreach ($article->get('photos')->referencedEntities() as $media) {
      $file = $media->get('field_newsletter_image')->entity;
      if (!$file) { continue; }
      $url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
      $build['photos'][] = ['#type' => 'container', 'image' => ['#theme' => 'image', '#uri' => $file->getFileUri(), '#alt' => $media->label()], 'download' => Link::fromTextAndUrl('Originele foto openen', Url::fromUri($url))->toRenderable()];
    }
    return $build;
  }
}
