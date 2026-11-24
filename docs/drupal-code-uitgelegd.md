# De KSA code uitgelegd voor een beginnende Drupalontwikkelaar

Deze handleiding beschrijft de KSA-site zoals die op 20 oktober 2026 is gebouwd, met codebasis `ae9ffe9`. Je leert waar inhoud leeft, hoe Drupal die op het scherm krijgt, welke eigen code daarbij betrokken is en waar je iets moet aanpassen. De [code-audit](code-audit-2026-10-20.md) bespreekt wat nog beter kan.

## 1 De vier onderdelen van de site

De site bestaat uit **code, configuratie, inhoud en bestanden**. Die moet je uit elkaar leren houden.

| Onderdeel | Voorbeeld in onze site | Waar staat het? |
|---|---|---|
| Code | PHP-controller, Twig-kaart, JavaScript voor Jos | Bestanden in Git |
| Configuratie | Welke velden een ploeg heeft; hoe een View sorteert | Actief in de database, exporteerbaar als YAML |
| Inhoud | De naam van een ploeg, een nieuwsbericht, een ingevulde pagina-layout | Database |
| Bestanden | Groepsfoto, PDF-rondeboekje | Publieke of private bestandenmap; registratie in database |
| Omgevingsgegevens | Databaseverbinding, private opslaglocatie | Serverinstellingen buiten Git |
| Runtimegegevens | Laatste agendafeed, toegangssleutelhash, sessie | Onder andere State, KeyValue en sessieopslag |

Een wijziging aan `card.css` gaat mee met Git. Een nieuwe foto die je via Drupal uploadt niet. Een ander veldwidget wordt actieve configuratie; pas een gecontroleerde export zet dat ook in Git. Dat verklaart waarom “push de code” niet hetzelfde is als “kopieer de hele site”. Zie ook [Drupals configuratiebeheer](https://www.drupal.org/docs/administering-a-drupal-site/configuration-management/managing-your-sites-configuration).

## 2 Waar je begint in de mappen

| Pad vanaf de projectmap | Betekenis |
|---|---|
| `composer.json` | Welke Drupalmodules en PHP-bibliotheken we nodig hebben |
| `composer.lock` | De precieze vastgelegde pakketversies |
| `.ddev/` | Lokale PHP-, webserver- en databaseomgeving |
| `web/` | Publieke documentroot van de webserver |
| `web/core/` | Drupal zelf, geïnstalleerd door Composer |
| `web/modules/contrib/` | Bestaande modules van andere Drupalontwikkelaars |
| `web/modules/custom/` | Onze vier eigen modules |
| `web/themes/custom/ksa/` | Het uiterlijk en de interactie van de publieke site |
| `config/sync/` | Gecontroleerde configuratie-export; momenteel niet volledig actueel |
| `scripts/` | Technische controles |
| `docs/` | Ontwerpkeuzes, gebruik en technische uitleg |
| `translations/` | Aanvullende Nederlandse interfacevertalingen |
| `vendor/` | PHP-afhankelijkheden van Composer |

Pas `core`, `contrib` en `vendor` niet rechtstreeks aan. Een volgende Composer-installatie kan zulke wijzigingen overschrijven. Uitbreiden gebeurt met configuratie, een eigen module of een template in het eigen thema.

Lokaal gebruiken we DDEV met PHP 8.3. De gecontroleerde installatie draait Drupal 11.4.10. Gebruik PHP en Composer via DDEV. De oudere versie in `CLAUDE.md` is historische documentatie.

## 3 De Drupalbegrippen in ons project

Een **entity** is een object dat Drupal opslaat en waarvoor het gemeenschappelijke functies aanbiedt. Een node, gebruiker, Media-item en nieuwsbriefartikel zijn entities.

Een **bundle** is een subtype van zo'n entity. Onze node-bundles zijn `page`, `article`, `team` en `leader`. In de interface heten die Basispagina, Nieuwsbericht, Ploeg en Leider. Een **field** bevat een eigenschap, zoals `field_email`.

Een **widget** bepaalt hoe je een veld invult. Een **formatter** bepaalt hoe een opgeslagen veld verschijnt. Voorbeeld: een Media Library-widget laat je een foto kiezen; een afbeeldingsformatter bepaalt hoe Drupal die foto toont.

Een **view mode** is een benoemde presentatie van één entity. Een ploeg heeft bijvoorbeeld een teaser, contactkaart, downloadkaart en volledige pagina. De gegevens blijven dezelfde.

Een **View** met hoofdletter is een instelbare lijst: welke entities ophalen, welke filters toepassen, in welke volgorde en met welke paginering. Een View kan vervolgens elke entity in een gekozen view mode tonen.

Aansluitend:

| Begrip | Concrete toepassing |
|---|---|
| Block | Een plaatsbaar onderdeel, zoals een hero of de agenda |
| Block content entity | Een blok met opgeslagen velden, zoals tekst en foto |
| Block-plugin | PHP die een blok berekent, zoals komende activiteiten |
| Layout Builder | Secties, kolommen en blokken op een pagina plaatsen |
| Route | Koppeling tussen URL, uitvoerende code en toegangsvoorwaarden |
| Controller | Bouwt het antwoord op een route |
| Form API | Drupal beschrijft, toont, valideert en verwerkt formulieren |
| Service | Herbruikbare functieklasse, bijvoorbeeld de agendafeed ophalen |
| Hook | Uitbreidingspunt: voer onze code uit wanneer Drupal iets doet |
| Event subscriber | Reageert op gebeurtenissen zoals een HTTP-antwoord |
| Revision | Bewaarde versie van een entity |
| SDC | Componentmap met beschrijving, Twig, CSS en eventueel JavaScript |

## 4 Wat er gebeurt als je Leiding opent

Een verzoek gaat vereenvoudigd door deze stappen:

```text
Browser vraagt /leiding
  → Drupal herkent de route achter de URL-alias
  → Drupal controleert toegang
  → De pagina en haar Layout Builder-blokken worden opgebouwd
  → De teams-View selecteert gepubliceerde ploegen
  → Elke ploeg wordt in teaserweergave opgebouwd
  → Het node-template geeft de velden aan de kaartcomponent
  → Twig maakt HTML, CSS geeft de vorm
  → JavaScript voegt alleen benodigde interactie toe
```

De bestanden bij deze keten zijn:

- `config/sync/views.view.teams.yml`: selectie en volgorde.
- `templates/views/views-view--teams.html.twig`: omlijst de lijst.
- `templates/content/node--team--teaser.html.twig`: koppelt ploegvelden aan de kaart.
- `components/team-overview/`: breedte en afstand van het overzicht.
- `components/card-grid/`: kolommen en tussenruimte.
- `components/card/`: foto, titel, plaats, leeftijd en link.

Die themapaden beginnen bij `web/themes/custom/ksa/`.

Wil je een ploeg verbergen, dan wijzig je de publicatiestatus in Drupal. Wil je alle kaarten even hoog, dan kijk je in CSS. Wil je een andere sorteervolgorde, dan kijk je naar de View en `field_weight`. Je hoeft voor deze drie veranderingen dus niet op dezelfde plaats te zijn.

## 5 De inhoudsstructuur

| Model | Belangrijkste gegevens | Waar gebruikt |
|---|---|---|
| `page` | Titel, intro, tekst, eigen Layout Builder-inhoud | Homepage en informatiepagina's |
| `article` | Titel, intro, hoofdafbeelding via Media, tekst, datum | Nieuws en nieuwsdetail |
| `team` | Foto, locatie, leeftijd, e-mail, omschrijving, volgorde, logo, contacttitel, rondeboekje | Leiding, contact en rondes |
| `leader` | Foto, telefoon, babysitindicatie, ploegverwijzing | Leiders binnen een ploeg |
| Media `image` | Afbeeldingsbestand en alt-tekst | Gewone sitefoto's |
| Media `document` | PDF-bestand | Rondeboekjes |
| Media `newsletter_photo` | Privéafbeelding | Nieuwsbriefinzendingen |
| `newsletter_article` | Titel, schrijver, tekst, instuurdatum, activiteitsdatum, foto's | Interne nieuwsbriefverzameling |

Een File-entity registreert het fysieke bestand. Media geeft dat bestand een beheerbaar inhoudsobject. Een ploeg verwijst bijvoorbeeld naar document-Media, en dat Media-item verwijst naar de PDF.

```text
Ploeg.field_booklet → Media document → File → public://...
Nieuwsbriefartikel.photos → Media newsletter_photo → File → private://newsletter/...
```

Een verborgen ploeg blijft opgeslagen. De Views voor de publieke lijsten filteren op publicatiestatus. Dat is waarom Plus16 uit overzichten kan verdwijnen zonder de gegevens te verliezen. Een reeds bekende URL naar een publiek bestand wordt niet automatisch privé door de ploeg te verbergen.

## 6 Hoe je YAML leest

Dit is een vereenvoudigde route zoals we die gebruiken:

```yaml
ksa_editor.home:
  path: '/admin/leiding'
  defaults:
    _controller: '\Drupal\ksa_editor\Controller\EditorController::home'
    _title: 'Site beheren'
  requirements:
    _permission: 'access content overview'
  options:
    _admin_route: TRUE
```

De eerste regel is de interne routenaam. `path` is de URL. `_controller` noemt de PHP-methode. `_permission` bepaalt wie toegang krijgt. `_admin_route` laat Drupal de route als beheerpagina behandelen.

Andere YAML-soorten:

| Bestandsnaam | Doel |
|---|---|
| `*.info.yml` | Module/thema aanmelden, afhankelijkheden noemen |
| `*.routing.yml` | URL's en toegangsvoorwaarden |
| `*.services.yml` | Services en hun afhankelijkheden registreren |
| `*.permissions.yml` | Rechten die je aan rollen kunt toekennen |
| `*.links.menu.yml` | Menulinks naar routes |
| `*.links.task.yml` | Tabbladen zoals bekijken en bewerken |
| `*.libraries.yml` | CSS/JS-bundels en afhankelijkheden |
| `config/schema/*.schema.yml` | Typen van configuratiewaarden beschrijven |
| `config/install/*.yml` | Beginconfiguratie voor installatie van een module |
| `*.component.yml` | Contract van een SDC-component |

Configuratiebestanden zijn vaak lang omdat Drupal veel opties expliciet exporteert. Een lange Views-export is niet hetzelfde als veel zelfgeschreven applicatielogica.

## 7 Het thema van dichtbij

### Structuur en verantwoordelijkheid

`ksa.info.yml` benoemt het thema, de regio's header/content/footer en de basisbibliotheek. `ksa.libraries.yml` laadt globale fonts, tokens, basisstijl, layouts en paginering.

`ksa.theme` bevat twee kleine preprocess-aanpassingen: de paginatitel weet of dit de homepage is, en ploeg-/leiderdetails houden het leidingmenu actief. Die laatste koppeling gebruikt nu een vast node-ID; dat is een verbeterpunt.

`templates/layout/page.html.twig` tekent header, main en footer. De loginvariant plaatst het bestaande Drupalformulier naast Jos. Het wachtwoordhersteltemplate hergebruikt die indeling.

### Een Twig-template is de koppeling

In `node--team--teaser.html.twig` staat onder andere:

```twig
{% embed 'ksa:card' with {url: url, variant: 'team'} %}
  {% block image %}{{ content.field_photo }}{% endblock %}
  {% block title %}{{ label }}{% endblock %}
  {% block meta %}
    {{ content.field_location }}{{ content.field_age_range }}
  {% endblock %}
{% endembed %}
```

`content.field_photo` is een door Drupal opgebouwd veld, geen losse bestandsnaam. Drupal kan daarmee de gekozen formatter, toegang en cachegegevens meenemen. `embed` gebruikt de bestaande kaart en vult haar vakken. `variant` is een eenvoudige keuze voor het uiterlijk.

Een **prop** is zo'n gewone waarde, bijvoorbeeld `variant: team`. Een **slot** is een stuk te renderen inhoud, bijvoorbeeld de foto. De `.component.yml` beschrijft welke invoer de component verwacht. Dit volgt Drupals [Single Directory Components](https://www.drupal.org/docs/develop/theming-drupal/using-single-directory-components/creating-a-single-directory-component).

### Alle componenten

Elke map hieronder staat onder `web/themes/custom/ksa/components/`.

| Component | Wat die doet |
|---|---|
| `site-header` | Headerblokken, mobiel menu, openen/sluiten en focus |
| `site-footer` | Logo, twee footermenu's, copyright, loopstrook voor Jos |
| `hero` | Hoofdkop, achtergrond en fotowaslijn met carrouselbediening |
| `card` | Gedeelde kaart voor nieuws, ploeg, leider of activiteit |
| `card-grid` | Responsief raster met 3/4 desktopkolommen en 1/2 mobiele kolommen |
| `news-overview` | Nieuwslijst met sectiekop en paginering |
| `team-overview` | Overzicht van ploegen |
| `contact-overview` | Raster met contactkaarten |
| `article-detail` | Wrapper rond Layout Builder-inhoud van een nieuwsbericht |
| `team-detail` | Wrapper rond ploegdetail en enkele andere detailpresentaties |
| `page-intro` | Intro onder de paginatitel |
| `contact-card` | Titel, e-mail, optionele telefoon en decoratief logo |
| `download-card` | Klikbare PDF-kaart |
| `info-card` | Foto/tekstkaart, of tekstkaart wanneer de foto ontbreekt |
| `photo` | Losse foto binnen een sectie |
| `rich-text` | Typografie van opgemaakte tekst en daarin geplaatste media |
| `text-media` | Banner, stap of tab met tekst en afbeelding |
| `media-strip` | Automatisch schuivende fotostrook |
| `agenda` | Activiteitenkaarten, abonneerlinks, pager en Google-maandweergave |
| `login-page` | Indeling van het accountformulier naast de mascotte |
| `jos` | SVG-mascotte, scenario's en beweging |
| `jos-login` | Mascotte die op muis, naam, wachtwoordfocus en fouten reageert |
| `newsletter-fox` | Hergebruik van de tekening naast het instuurformulier |
| `newsletter-received` | Bedanktbericht na een geslaagde inzending |

De 44 Drupal-templates zijn gegroepeerd in `block`, `content`, `field`, `layout`, `navigation` en `views`. De meeste leveren uitsluitend de juiste velden of lijsten aan bovenstaande componenten. De `field`-templates halen bijvoorbeeld overbodige wrappers weg rond hero-elementen. De menutemplates tonen bestaande Drupal-menu-items; ze slaan de menu-inhoud niet zelf op.

### CSS en afbeeldingen

`css/tokens.css` is de gedeelde bron voor kleuren, lettergroottes, breedtes en afstanden. `base.css` regelt basisgedrag zoals box-sizing en focus. `layout.css` verbindt componenten met Layout Builder-secties.

Een Drupal image style bepaalt welke afgeleide afbeelding wordt gemaakt. CSS bepaalt hoe die in het zichtbare kader wordt gezet. `object-fit: cover` vult het kader en kan delen afsnijden; `contain` toont het hele beeld met eventueel lege ruimte. Als Drupal de bron al heeft bijgesneden, kan CSS dat niet terughalen.

Voor ploegkaarten gebruikt onze CSS een gelijk beeldkader met `contain`, plus flexbox en gelijke gridrijen. De fotoverhouding hoeft dus niet voor elke upload identiek te zijn.

### JavaScript en Drupal behaviors

Onze scripts gebruiken doorgaans:

```javascript
Drupal.behaviors.voorbeeld = {
  attach(context) {
    once('voorbeeld', '[data-voorbeeld]', context).forEach(init);
  },
};
```

Drupal kan `attach` opnieuw aanroepen na AJAX. `once` voorkomt dubbele initialisatie. `context` begrenst het nieuwe stuk pagina. `detach` ruimt listeners, observers en timers op wanneer een onderdeel verdwijnt.

De hero pauzeert bij interactie, een verborgen tab en de voorkeur voor minder beweging. De header verplaatst dezelfde menublokken naar een mobiel dialoogvenster. De stappen/tabbladen worden pas na het laden van HTML verrijkt; zonder JavaScript blijft de inhoud leesbaar. In Layout Builder worden die stappen niet verborgen, zodat de beheerder elk blok kan bewerken.

Jos bestaat uit SVG-tekening, CSS-bewegingen en JavaScript-regie. Het hero-dagschema staat in `jos.js`; de footer heeft eigen muisvolging in `site-footer.js`. Dit zijn presentatie-effecten, geen Drupal-inhoudsopslag.

## 8 Module KSA Leiding

Map: `web/modules/custom/ksa_editor/`.

Het doel is een rustige beheerervaring zonder de leiding toegang te geven tot technische instellingen of de paginaopbouw.

| Bestand | Verantwoordelijkheid |
|---|---|
| `ksa_editor.module` | Hooks voor logging, verborgen formuliervelden, zichtbaarheid van groepen, navigatie en contextual links |
| `src/Controller/EditorController.php` | Dashboard, inhoudslijsten en medialijst |
| `src/Access/EditorLayoutAccess.php` | Extra toegangscontrole voor Layout Builder |
| `src/Routing/EditorRoutes.php` | Voegt die controle toe aan bestaande Drupalroutes |
| `src/EventSubscriber/EditorLanding.php` | Stuurt de eigen profielpagina van leiding naar het dashboard |
| `src/LeadershipLog.php` | Maakt voor/na-snapshots en bewaart die in Entity Log |
| `src/Controller/LeadershipLogController.php` | Haalt logregels op en toont de vergelijking |
| `src/Form/LeadershipLogFilter.php` | GET-formulier voor zoekterm, gebruiker, type en datum |
| `css/editor.css` | Dashboard en actieknoppen |
| `css/audit.css` | Loglayout met uitklapbare voor/na-panelen |

De drie logginghooks reageren op `insert`, `update` en `delete`. `record()` bepaalt of het om de juiste gebruiker en entity gaat. Bij updates vergelijkt het de oorspronkelijke en nieuwe waarden. `value()` zet Media- en File-verwijzingen om naar begrijpelijke namen en URI's. Geheimen en wachtwoordvelden worden uitgesloten.

De UI-verberging is niet de enige beveiliging. `EditorLayoutAccess` controleert onder meer dat de pagina bewerkbaar is en dat het bloktype in de toegestane lijst staat. Reset wordt geweigerd. De route subscriber koppelt dit beleid aan de bestaande Layout Builder-routes.

`ksa_editor_preserve_block_styles()` voorkomt dat het stijl-submitgedrag alsnog stijlen verandert terwijl die invoer verborgen is. Dit is een voorbeeld van een kleine gerichte uitzondering die je niet zomaar door CSS-verberging kunt vervangen.

## 9 Module KSA Agenda

Map: `web/modules/custom/ksa_agenda/`.

Google Agenda blijft de bron. Drupal maakt geen tweede handmatig te onderhouden evenementendatabase.

| Bestand | Wat het doet |
|---|---|
| `CalendarId.php` | Herkent een ID, embedlink, iframecode of openbare ICS-link en normaliseert die; weigert geheime ICS-adressen |
| `AgendaFetcher.php` | Haalt de feed op, bewaart de laatste geslaagde resultaten en geeft komende activiteiten terug |
| `EventParser.php` | Gebruikt `sabre/vobject` voor herhalingen en zet events om naar eenvoudige arrays in Brusselse tijd |
| `DateLabel.php` | Maakt Nederlandse datumlabels |
| `EventIcs.php` | Maakt een downloadbaar kalenderbestand van één activiteit |
| `Hook/AgendaHooks.php` | Vernieuwt de feed tijdens cron |
| `Plugin/Block/AgendaBlock.php` | Bouwt de kaarten, kalender en pager; vijf activiteiten per pagina |
| `Controller/EventController.php` | Levert het ICS-bestand of een 404 |
| `Form/SettingsForm.php` | Beheert de kalenderkeuze en toont synchronisatiestatus |
| `StackMiddleware/AgendaDay.php` | Maakt agendacaches ongeldig bij de eerste aanvraag op een nieuwe Brusselse dag |

`AgendaFetcher::refresh()` haalt niet bij elk paginabezoek Google op. Cron of het opslaan van instellingen doet dat. Bij een fout blijven de vorige gegevens bewaard. `upcoming()` laat afgelopen activiteiten weg; een meerdaags kamp blijft zichtbaar zolang het loopt. `find()` zoekt de activiteit voor de downloadroute.

`EventParser` laat de ingewikkelde herhalingsregels aan een bestaande bibliotheek over. Onze code zorgt vooral voor annuleringen, sortering, tijdzone en het inclusieve einde van een meerdaagse activiteit.

De middleware is nodig omdat een volledig gecachte pagina PHP-rendercode kan overslaan. Ze controleert de dag vóór de interne paginacache antwoordt en invalideert alleen de agendatag. Een externe CDN-cache vraagt nog een eigen cachebeleid.

## 10 Module KSA SEO

Map: `web/modules/custom/ksa_seo/`, met één klein `.module`-bestand.

- `ksa_seo_views_post_execute()` controleert de opgevraagde pagina van de nieuws-View. Ongeldige of te hoge paginanummers geven 404.
- `ksa_seo_metatags_alter()` geeft elke geldige archiefpagina een passende canonical en Open Graph-URL.
- `ksa_seo_page_attachments_alter()` laat de cache rekening houden met het paginanummer.

De rest is configuratie van bestaande modules: Metatag voor metadata, Schema Metatag voor gestructureerde gegevens, Pathauto voor URL-patronen, Redirect voor redirects en Simple Sitemap voor sitemapgeneratie. We hebben dus geen eigen algemeen SEO-systeem gebouwd.

## 11 Module KSA Nieuwsbrief

Map: `web/modules/custom/ksa_newsletter/`.

Deze module verzamelt bijdragen. Ze stelt geen e-mailnieuwsbrief samen en verstuurt die niet. Een andere app leest de gegevens via de API.

### De entity en de beheerinterface

`Entity/NewsletterArticle.php` beschrijft het eigen entiteitstype. Het attribute `#[ContentEntityType(...)]` vertelt Drupal welke tabellen, velden, handlers, routes en revisiegegevens erbij horen.

De velden:

| Veld | Functie |
|---|---|
| `id` | Intern numeriek ID |
| `uuid` | Stabiele unieke identificatie, ook voor de API |
| `title` | Titel van de bijdrage |
| `writer` | Door de inzender opgegeven naam |
| `content` | Opgemaakte tekst met Drupal-tekstformaat |
| `body` | Oud tekstveld voor compatibiliteit; nog aanwezig |
| `activity_date` | Optionele datum van de activiteit |
| `created` | Instuurmoment, bruikbaar om een editie te selecteren |
| `uid` | Ingelogde inzender, of anonieme gebruiker |
| `photos` | Maximaal vijf verwijzingen naar privé-Media |
| Revisievelden | Versie-ID, uitvoerder, tijd en toelichting |

De entity gebruikt standaard `EntityViewBuilder`, `EntityViewsData`, verwijderformulier en revisieherstel. `ArticleForm` is een dunne uitbreiding op `ContentEntityForm`: bij beheerwijzigingen wordt een nieuwe revisie bewaard.

`NewsletterAccess` koppelt bekijken, wijzigen, verwijderen en revisies aan eigen permissies. `internal: TRUE` maakt deze entity niet automatisch beschikbaar als generieke JSON:API-resource. Het is geen vervanging voor toegangscontrole; die bestaat daarnaast.

Via `/admin/structure/nieuwsbrief` beheer je instellingen en de veld-/formulier-/weergavetabbladen. Basisvelden zijn in code gedefinieerd; extra velden kunnen via Field UI. De openbare instuurvelden en de API nemen nieuwe adminvelden niet automatisch over.

### Alle ondersteunende klassen

| Bestand | Functie |
|---|---|
| `Controller/NewsletterController.php` | Instuurpagina, bedanktbericht, beheerwrapper rond de View en detailweergave |
| `Form/SubmissionForm.php` | Publiek instuurformulier en verwerking |
| `SubmissionGate.php` | Gedeelde link, sessietoegang en instuurquota |
| `Form/LinkForm.php` | Instuurlink tonen en vernieuwen |
| `Form/ApiKeyForm.php` | API-sleutel aanmaken, vervangen en intrekken |
| `ApiAccess.php` | HTTPS, Bearer-sleutel en API-quota controleren |
| `Controller/ApiController.php` | Artikelen als JSON en beschermde foto-downloads |
| `Form/PublishPhotosForm.php` | Foto's expliciet selecteren voor openbare publicatie |
| `PublicPhotos.php` | Openbare kopieën maken en de koppeling bewaren |
| `NewsletterText.php` | Tekst via Drupalfilters renderen; legacytekst normaliseren |
| `Form/SettingsForm.php` | Tekstformaat, fotoafmetingen en quota beheren |
| `Settings.php` | Configuratie lezen met terugvalwaarden |
| `EventSubscriber/PrivateResponse.php` | Antwoorden markeren als privé, niet cachen en niet indexeren |
| `ksa_newsletter.module` | Media-/bestandsrechten, privéstatus, Media Library-aanpassing en publicatie-informatie |
| `ksa_newsletter.install` | Opslageisen en bestaande schema-/gegevensmigraties |
| `css/newsletter.css` | Opmaak van formulier en foto-overzichten |

### Van instuurlink tot opgeslagen bijdrage

1. De schrijver opent een link met een toegangstoken.
2. `NewsletterController::entry()` laat `SubmissionGate::accept()` het token controleren.
3. De sessie krijgt een afgeleide toegangscode en de browser wordt naar een URL zonder token doorgestuurd.
4. Bij latere stappen controleert `allowed()` of die sessietoegang nog overeenkomt met de actuele link, of dat de ingelogde gebruiker instuurrecht heeft.
5. `SubmissionForm::buildForm()` bouwt de velden, CKEditor en Honeypot.
6. `validateForm()` controleert sessie, CSRF-token, quota, tekst en uploads.
7. `submitForm()` bewaart de bestanden privé, maakt Media en daarna het artikel.
8. Een redirect toont één keer het bedanktbericht.

De UUID van de inzending voorkomt dat dezelfde POST-herhaling twee artikelen maakt. Een lock voorkomt dat twee gelijktijdige pogingen dezelfde kritieke stap uitvoeren. Foutafhandeling probeert reeds aangemaakte Media en bestanden op te ruimen.

Bij linkrotatie veranderen de actuele tokengegevens. Daardoor vallen oude sessies vanzelf door de volgende toegangscontrole. De link is uitsluitend toestemming om in te sturen, niet om ingezonden artikelen te lezen.

### Tekstopmaak en veiligheid

De werkbalk komt uit core CKEditor5 en `editor.editor.newsletter`. Het toegestane HTML-formaat komt uit `filter.format.newsletter`. De invoer mag niet zelf een ander tekstformaat kiezen.

`NewsletterText::render()` gebruikt een `processed_text`-renderarray. Daardoor past Drupal de opgeslagen tekstfilters toe voordat HTML naar de detailpagina of API gaat. Voor oude `body`-gegevens bestaat nog `clean()`.

`#plain_text` betekent: behandel deze waarde als gewone tekst. `#markup` is bedoeld voor markup en heeft eigen renderfiltering. Twig escape't gewone waarden standaard. Gebruik `|raw` nooit voor ongecontroleerde inzendertekst. De nieuwsbriefvos gebruikt het alleen bij een door ons beheerd lokaal SVG-bestand.

### Privéfoto en openbare nieuwsbriefkopie

Een privéfoto is bestemd voor beheer en de geautoriseerde app. Een mailprogramma kan geen eigen Bearer-sleutel bij een afbeelding meesturen. Daarom maken we voor geselecteerde foto's een aparte openbare kopie.

`PublicPhotos::publish()`:

1. Controleert het bronmediatype en de private bestandslocatie.
2. Neemt een lock op de bron.
3. Hergebruikt een bestaande kopie, tenzij een nieuwe versie gevraagd is.
4. Schaalt met Drupals image toolkit zonder uitsnijden of vergroten.
5. Zet WebP om naar PNG; JPEG blijft JPEG.
6. Geeft de kopie een nieuwe UUID-bestandsnaam.
7. Registreert File en openbare afbeelding-Media.
8. Bewaart de relatie in KeyValue.

`get()` vindt de bestaande kopie als de oorspronkelijke File-UUID nog overeenkomt en het kopiebestand bestaat. De publicatiefunctie behoudt eerdere bestanden. Beheer buiten die functie verdient nog extra bescherming, zoals de audit beschrijft.

De hook `ksa_newsletter_media_presave()` houdt alle originelen ongepubliceerd. `ksa_newsletter_file_download()` beschermt de private bestanden en afgeleide privébeelden. `ksa_newsletter_views_pre_view()` past uitsluitend de bevoegde nieuwsbriefselectie in Media Library aan, zodat die ongepubliceerde originelen daar wel te kiezen zijn.

### De API

De artikelenroute is `GET /api/nieuwsbrief/artikelen`. De client stuurt een sleutel in de `Authorization`-header, nooit in de URL.

`ApiAccess::check()` geeft bij ongeldige toegang direct een JSON-foutantwoord. Alleen daarna leest de controller artikelen. `_access: TRUE` in de routedefinitie betekent hier dus niet dat de gegevens vrij toegankelijk zijn: de eigen check staat vooraan in beide API-methoden.

De filters `van` en `tot` gebruiken de instuurdatum in Brusselse tijd. `limit` begrenst de paginagrootte. `after` is een cursor op intern ID. `pagination.next` geeft de volgende opvraaglink.

Elk artikel bevat UUID, titel, schrijver, `text_html`, instuurdatum, eventuele activiteitsdatum en foto's. Per foto zijn er:

- `url` en `download_url`: beschermd; dezelfde API-sleutel vereist.
- `public_url`: alleen gevuld als een openbare kopie bestaat.
- `public_image`: informatie over die kopie, inclusief afmetingen en type.
- Bestandsnaam, UUID, MIME-type, bestandsgrootte en oorspronkelijke afmetingen.

`photo()` controleert dat de foto echt aan het opgegeven artikel hangt en dat het fysieke bestand binnen de private nieuwsbriefmap ligt. De volledige contractbeschrijving staat in [nieuwsbrief-api.md](nieuwsbrief-api.md).

### Installatie en updates

De `.install`-code controleert of private opslag buiten de publieke webmap staat. Update `11001` introduceert revisieopslag; `11002` vult het nieuwe tekstveld met behoud van oude gegevens; `11003` vult historische revisiemetadata aan.

Dat zijn eenmalige migraties voor bestaande installaties. Verwijder ze niet enkel omdat ze al uitgevoerd zijn: andere kopieën van de site kunnen die geschiedenis nog nodig hebben.

## 12 Caching begrijpen

Een renderarray kan behalve inhoud ook cache-informatie dragen:

```php
[
  '#plain_text' => 'Voorbeeld',
  '#cache' => [
    'tags' => ['node:33'],
    'contexts' => ['user.permissions'],
    'max-age' => 3600,
  ],
]
```

Dit is een illustratie, geen nieuw toegevoegde projectcode.

- **Tags:** wat moet deze cache ongeldig maken? Bijvoorbeeld een gewijzigd artikel.
- **Contexts:** voor welke omstandigheden moet een afzonderlijke versie bestaan? Bijvoorbeeld andere rechten of een ander paginanummer.
- **Max-age:** hoe lang mag het resultaat bewaard blijven?

De agenda gebruikt tags voor kalendergegevens en een context voor de pager. SEO gebruikt de queryparameter `page`. Privé-nieuwsbriefpagina's en API-antwoorden zijn bewust niet publiek cachebaar.

Cache uitschakelen kan voor een klein privébeheeroverzicht verstandig zijn. Overal `max-age: 0` toevoegen aan openbare pagina's zou een dure omweg zijn. Render arrays en standaard veldformatters helpen Drupal de juiste informatie automatisch door te geven.

## 13 Welke bestaande modules doen het werk

| Module of bibliotheek | Gebruik |
|---|---|
| Core Node, Field, Text | Publieke inhoud en velden |
| Core Media en Media Library | Foto's en PDF's beheren/selecteren |
| Core Views | Publieke lijsten en nieuwsbriefbeheer |
| Core Layout Builder | Paginaopbouw |
| Core CKEditor5 en Filter | Bewerkbare, gefilterde opmaak |
| Core Navigation en Gin | Beheeromgeving |
| Layout Builder Perms | Fijnere bewerkrechten voor layouts |
| Layout Builder Styles | Benoemde stijlen voor secties en blokken |
| View Unpublished | Leiding kan verborgen inhoud terugvinden |
| Simplify | Technische formulieronderdelen verbergen |
| Entity Log | Opslag en basisfuncties voor veldlogs |
| Honeypot | Spamremming op het instuurformulier |
| Metatag, Schema Metatag | SEO-metadata en gestructureerde gegevens |
| Pathauto, Redirect | Leesbare URL's en redirects |
| Simple Sitemap | Sitemap |
| Single Content Sync | Hulpmiddel voor inhoudsoverdracht |
| `sabre/vobject` | Kalenderbestanden en herhalingen |
| Drush | Technische CLI voor controles, updates en cache |

Een pakket in `composer.json` is niet noodzakelijk een actieve module. De actieve modulelijst staat in Drupalconfiguratie. De oude toolbar-modules zijn lokaal nog actief en zijn opruimkandidaten, niet een reden om Navigation te vervangen.

## 14 Testen en fouten zoeken

| Scriptgroep in `scripts/` | Wat wordt gecontroleerd? |
|---|---|
| `check-calendar-id`, `check-date-label` | Kalenderinvoer en Nederlandse datums |
| `check-event-parser`, `check-event-ics` | Herhalingen, annuleringen, tijdzones, downloads |
| `check-agenda-day` | Dagovergang vóór paginacache |
| `check-editor-access` | Rechten van leiding en bezoekers |
| `check-leadership-log` | Voor/na, bestanden, veilige uitvoer en logrechten |
| `check-seo`, `seo-response` | SEO en HTTP-responscontrole |
| `check-newsletter` | Instuurbeveiliging en basisgedrag |
| `check-newsletter-validation` | Ongeldige invoer en uploadgrenzen |
| `check-newsletter-api` | API-authenticatie, uitvoer en filters |
| `check-newsletter-public` | Openbare kopieën en vaste URL's |
| `check-newsletter-admin` | Revisies, Views, rechten en beheerintegratie |

Lees de bovenkant van een script voordat je het draait. Sommige gebruiken tijdelijke databasewrites of wisselen sleutelgegevens voor testdoeleinden. De rechtencontrole is alleen-lezen; de overige scripts zijn niet allemaal geschikt om zomaar op een gebruikte server te starten.

Voor toekomstige uitbreidingen zijn PHPUnit Unit-, Kernel-, Functional- en FunctionalJavascript-tests de Drupal-route. Die kunnen eigen fixtures opbouwen zodat bestaande inhoud en vaste node-ID's niet nodig zijn.

Als iets niet werkt, zoek van binnen naar buiten:

1. Staat de inhoud goed opgeslagen?
2. Is het veld zichtbaar in de juiste weergave?
3. Filtert de View het item weg?
4. Heeft de gebruiker toegang?
5. Krijgt het Twig-template het verwachte veld?
6. Is het er wel, maar verbergt CSS of JavaScript het?
7. Is een oude cache nog zichtbaar?

Een cache rebuild kan gewijzigde code of route-/plugininformatie zichtbaar maken, maar herstelt geen verkeerde permissies of ontbrekende inhoud.

## 15 Veelvoorkomende aanpassingen

| Je wilt… | Begin hier |
|---|---|
| Een ploeg verbergen | Ploeg bewerken → Zichtbaarheid |
| Een rondeboekje vervangen | Document-Media uploaden en aan ploeg koppelen |
| De nieuwsvolgorde veranderen | Nieuws-View |
| Groepsfoto's volledig tonen | Drupal image style én `components/card/card.css` |
| Een homepagefoto vervangen | Hero-blok in de pagina-layout |
| Tekstopmaakknoppen wijzigen | Tekstformaten en editors |
| Een nieuw intern nieuwsbriefveld | Nieuwsbrief → Velden beheren, daarna formulier/weergave |
| Dat nieuwe veld ook via de API leveren | `ApiController`, API-contract en tests |
| Een instuurveld toevoegen | `SubmissionForm`, entityveld, validatie en tests |
| Een kalenderactiviteit wijzigen | Google Agenda |
| De bronagenda wijzigen | Drupal agenda-instellingen |
| Menulinks aanpassen | Drupal-menu's |
| Meer of minder leidingrechten | Rollen/permissies én controle van eigen toegangsregels |
| Een veldwijziging terugzoeken | Leidinglogboek of entityrevisies, afhankelijk van type |

## 16 Een veilige leerroute

Begin met één ploegkaart: volg de View, de teaserweergave, het Twig-template en de kaart-CSS. Daarna begrijp je al een groot deel van de publieke site.

Volg vervolgens één kleine PHP-route: de agenda-instellingen zijn een duidelijk voorbeeld van `ConfigFormBase`. Lees daarna `AgendaFetcher` om services en dependency injection te begrijpen. Pas daarna is de nieuwsbriefstroom een goed onderwerp, omdat daarin toegang, sessies, bestanden, entities en API's samenkomen.

Oefen uitsluitend met fictieve lokale inhoud. Voeg bijvoorbeeld een intern nieuwsbriefveld toe, plaats het in de formulierweergave en kijk waarom dat nog niet automatisch een publiek instuurveld of API-veld is. Dat verschil begrijpen voorkomt veel onbedoelde uitbreidingen van toegang.

Een goede wijziging zet gedrag op de juiste plaats: selectie in Views, rechten in accesscontrole, opslag in entities/services, formulieren in Form API en presentatie in Twig/CSS. Minder eigen code is nuttig wanneer Drupal dezelfde verantwoordelijkheid betrouwbaar kan overnemen.
