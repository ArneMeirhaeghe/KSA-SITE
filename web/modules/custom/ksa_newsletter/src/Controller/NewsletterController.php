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
    $build = ['#cache' => ['max-age' => 0]];
    $build['help'] = ['#markup' => '<p>Alle bijdragen staan hier privé. Filter op instuurdatum om je nieuwsbrief samen te stellen.</p>'];
    foreach (['settings' => ['Instuurlink beheren', 'ksa_newsletter.settings'], 'api' => ['API-toegang beheren', 'ksa_newsletter.api_settings'], 'configuration' => ['Nieuwsbriefinstellingen', 'ksa_newsletter.configuration']] as $key => [$label, $route]) {
      $url = Url::fromRoute($route);
      if ($url->access()) { $build[$key] = Link::fromTextAndUrl($label, $url)->toRenderable() + ['#prefix' => '<p>', '#suffix' => '</p>']; }
    }
    $view = \Drupal\views\Views::getView('newsletter_articles');
    $build['articles'] = $view ? $view->buildRenderable('default') : ['#markup' => '<p>Het overzicht Nieuwsbriefartikelen moet nog worden ingesteld via Overzichten.</p>'];
    return $build;
  }
  public function detail(NewsletterArticle $newsletter_article): array {
    $article = $newsletter_article;
    $build = ['#cache' => ['max-age' => 0], '#attached' => ['library' => ['ksa_newsletter/form']]];
    $build['back'] = Link::createFromRoute('← Alle nieuwsbriefartikelen', 'ksa_newsletter.overview')->toRenderable();
    $build['article'] = ['#type' => 'container', '#attributes' => ['class' => ['newsletter-article-view']], 'entity' => $this->entityTypeManager()->getViewBuilder('newsletter_article')->view($article)];
    return $build;
  }
}
