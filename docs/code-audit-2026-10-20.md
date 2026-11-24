# Code audit van de KSA site

Onderzocht op 20 oktober 2026, op basis van commit `ae9ffe9` en de actieve lokale Drupal-installatie.

De site heeft een goede Drupal-basis, maar is nog niet overal zo onderhoudbaar en via het beheer instelbaar als mogelijk is. De grootste winst zit in configuratiebeheer, de handgeschreven beheerlijsten en de structuur van de nieuwsbriefcode. De vier eigen modules hoeven niet te verdwijnen. Vooral de afscherming van nieuwsbriefinzendingen en de beperking van Layout Builder hebben een concrete reden.

Deze audit verandert geen sitegedrag. De bevindingen hieronder zijn nog uit te voeren verbeteringen. De bijbehorende [juniorhandleiding](drupal-code-uitgelegd.md) legt uit hoe de bestaande code werkt.

## Omvang en bewijs

De review omvat de vier eigen modules, het KSA-thema met zijn templates en componenten, Composer-afhankelijkheden, relevante configuratie, controlescripts en bestaande documentatie. Externe Drupal- en Composer-pakketten zijn geen regel-voor-regel securityaudit ondergaan. Voor specifieke integraties is de lokaal geïnstalleerde core- en contrib-code nagekeken.

| Onderdeel | Bestanden met uitvoerbare code, CSS of Twig | Regels inclusief commentaar en witruimte |
|---|---:|---:|
| KSA Agenda | 10 | 896 |
| KSA Leiding | 10 | 654 |
| KSA Nieuwsbrief | 19 | 991 |
| KSA SEO | 1 | 51 |
| KSA-thema | 106 | 4.473 |
| Controlescripts | 14 | 1.062 |
| Totaal | 160 | 8.127 |

Deze telling omvat `.php`, `.module`, `.install`, `.theme`, `.css`, `.js` en `.twig`, geen YAML, SVG, lettertypen of afbeeldingen. Het thema bevat 24 SDC-componenten en 44 Drupal-templates. Regelaantallen zijn een inventaris, geen kwaliteitscijfer: compacte code met meerdere instructies per regel kan juist moeilijker te onderhouden zijn.

Tijdens deze audit uitgevoerd:

- PHP-syntaxcontrole: alle 52 betrokken PHP-, module-, install- en themebestanden slagen.
- Geïsoleerde agendatests: 58 controles slagen.
- Bestaande lokale leiding- en bezoekerscontrole: 416 van 416 controles slagen, zonder gebruikers of inhoud op te slaan.
- Alleen-lezenvergelijking tussen actieve lokale configuratie en `config/sync`.
- Toegangscontrole met een niet-opgeslagen Media-object: leiding mag een gewone openbare afbeelding van een andere eigenaar bewerken.
- Toegangscontrole met uitsluitend het algemene nieuwsbriefbeheerrecht: de overzichtsroute laat dit toe, de ingebedde View niet.

De nieuwsbriefintegratietests zijn inhoudelijk nagekeken maar niet opnieuw uitgevoerd in deze audit. Een deel wijzigt tijdelijk State, bestanden of databasegegevens. Eerdere testresultaten zijn geen nieuw uitgevoerde controle. Er is geen nieuwe visuele browsercontrole of controle van de actuele testserver uitgevoerd.

## Eerst oplossen

### 1 De configuratie in Git is geen volledige actuele momentopname

**Prioriteit: hoog voor herstel en volgende deployments.**

De repository bevat 428 configuratiebestanden. De actieve lokale site heeft 10 configuratiestukken die daar ontbreken en 19 die verschillen. De door Drupal ingestelde synchronisatieopslag bevat bovendien 0 items; de repositorymap en de geconfigureerde synchronisatieopslag zijn dus momenteel niet dezelfde bruikbare bron.

De 10 ontbrekende namen:

```text
editor.editor.newsletter
field.field.media.newsletter_photo.field_newsletter_image
field.storage.media.field_newsletter_image
filter.format.newsletter
honeypot.settings
ksa_newsletter.settings
media.type.newsletter_photo
single_content_sync.settings
system.action.content_bulk_export
views.view.newsletter_articles
```

Verschillen zitten onder andere in `core.extension`, de drie niet-administratieve rollen, de weergave `media.image.article_wide` en meerdere standaard-Views. De vergelijking is op volledige configuratiearrays gemaakt: niet elk verschil hoeft een functioneel probleem te zijn.

Een aantal nieuwsbriefbestanden staat wel in `ksa_newsletter/config/install`. Dat is nuttig bij de eerste installatie, maar vervangt geen actuele export voor een reeds geïnstalleerde site. Een `git pull` voert deze wijzigingen niet vanzelf uit.

**Aanpak:** kies en documenteer één synchronisatiemap; exporteer via de beheerinterface volgens de projectafspraak; beoordeel elk verschil; bewaar omgevingsspecifieke instellingen afzonderlijk. Test vervolgens herstel in een lege tijdelijke omgeving met de juiste database en bestanden. Geen blinde import van de huidige oude export.

### 2 Het leidinglogboek dekt nieuwsbriefartikelen niet

**Prioriteit: hoog als “alles wat leiding aanpast” de afspraak blijft.**

In [LeadershipLog.php](../web/modules/custom/ksa_editor/src/LeadershipLog.php), methode `record()`, staat een vaste lijst: `node`, `block_content`, `media`, `file`, `user`. `newsletter_article` ontbreekt. Leiding heeft lokaal wel `submit newsletter articles`.

Daardoor kan een ingelogde leider een nieuwsbriefartikel insturen zonder dat het artikel zelf in het leidinglogboek verschijnt. Media- en bestandswijzigingen kunnen wel afzonderlijk worden geregistreerd. Revisies van het nieuwsbriefartikel zijn een ander mechanisme en vullen dit overzicht niet automatisch aan.

**Aanpak:** leg de gewenste dekking expliciet vast en voeg dit entiteitstype, labels en filters toe. Test een inzending als leiding en een latere wijziging. Voor anonieme inzenders via de link is geen betrouwbare gebruikersidentiteit beschikbaar; de ingevulde schrijversnaam is geen geverifieerde login.

Het huidige log is ook geen permanent archief van de oude foto- of PDF-bytes. Het bewaart waarden, namen en URI's. Wordt het fysieke bestand later verwijderd, dan kan de oude link breken. Entity Log heeft daarnaast een eigen instelbare bewaarlimiet; spreek af welke geschiedenis bewaard moet blijven.

### 3 Openbare nieuwsbriefkopieën hebben geen aparte beheerbescherming

**Prioriteit: hoog voor het behoud van gepubliceerde fotolinks.**

[PublicPhotos.php](../web/modules/custom/ksa_newsletter/src/PublicPhotos.php) maakt openbare kopieën als gewone `image`-Media. De rol Leiding mag `edit any image media`. De eigen publicatiemethode overschrijft bestaande versies niet, maar de normale Media-bewerkroute valt niet onder die bescherming.

De koppeling tussen origineel en openbare kopie staat in KeyValue-opslag. Ze staat niet als zichtbaar entity-referenceveld op Media. Bij een wijziging via gewoon mediabeheer hoeft die koppeling daardoor niet mee te veranderen. Dit bewijst niet dat oude URL's nu kapot zijn; het toont dat de beloofde onveranderlijkheid alleen door de publicatieworkflow wordt bewaakt.

**Aanpak:** maak de publicatierelatie expliciet beheerbaar, bijvoorbeeld met een apart Media-type voor nieuwsbriefpublicaties en een verwijzing naar het origineel. Bescherm vervangen en verwijderen. Toon versies en gebruik op de beheerpagina. Migreer bestaande koppelingen zonder bestandsnamen of verzonden URL's te veranderen.

Een gewone image style is geen automatische vervanger: de bestaande eis is een vaste openbare kopie met een eigen versie, terwijl het origineel privé blijft.

### 4 Het algemene nieuwsbriefbeheerrecht en de View spreken elkaar tegen

**Prioriteit: middel, vóór een aparte nieuwsbriefbeheerrol wordt gebruikt.**

De overzichtsroute accepteert `administer newsletter articles` óf `view newsletter articles`. De View `newsletter_articles` eist uitsluitend `view newsletter articles`. Een lokaal aangemaakt accountobject met alleen het eerste recht kreeg toegang tot de route, maar niet tot de View. Er is geen gebruiker opgeslagen om dit te controleren.

De huidige administrator heeft ruime rechten en merkt dit waarschijnlijk niet. Een aparte rol die alleen het overkoepelende beheerrecht krijgt, kan wel tegen een leeg of geweigerd overzicht aanlopen.

**Aanpak:** laat route, View en entity hetzelfde beleid gebruiken, of ken leesrecht expliciet als vereist onderdeel van de beheerrol toe en documenteer dat. Neem deze combinatie op in de permissietests.

## Waar Drupal het maatwerk kan overnemen

| Onderdeel | Nu | Voorstel | Wat blijft eigen code? |
|---|---|---|---|
| Leidinglijsten | `EditorController::content()` en `media()` bouwen query's, rijen en knoppen zelf | Views met beperkte rechten, filters, paginering en passende operation-links | Klein dashboard en zo nodig een gerichte link naar de pagina-layout |
| Logoverzicht | Eigen GET-filterformulier en query; grote inline Twig-string | Views voor zoeken, datum, gebruiker en paginering; apart Twig-template of veldplugin voor voor/na | Snapshotlogica voor referenties, bestanden en inline blokken |
| Formulieren vereenvoudigen | Simplify-configuratie én brede `hook_form_alter` | Form modes/widgets voor veldkeuze, Simplify voor ondersteunde metadata, eigen alter alleen voor afwijkend gedrag | Beveiliging van opbouw en geblokkeerde velden |
| Instuurlink en API-bevestiging | Verplichte checkbox die Gin als schakelaar toont | Drupal `ConfirmFormBase` voor vervangen/intrekken | De feitelijke tokenrotatie |
| Downloadkaarten | Twig volgt `node → media → file` en bouwt de bestandslink | Gebruik een passende standaardformatter/view mode, of één kleine formatter als de kaart een directe PDF-link nodig heeft | Alleen de specifieke kaartpresentatie |
| Infokaarttekst | Twig splitst tekst op dubbele punten en maakt automatisch een deel vet | Opgemaakte tekst via CKEditor, of expliciete label/waardevelden indien nodig | CSS van de kaart |

De beheercontroller heeft ongeveer 60 regels aan lijstlogica die een goede kandidaat voor Views zijn, maar een nieuwe View voegt YAML-configuratie toe. Het doel is minder zelf te onderhouden gedrag en meer beheerbaarheid, niet noodzakelijk minder tekens in Git.

Views alleen kan de speciale voor/na-opslag niet vervangen. De gebruikte Entity Log-module registreert standaard updates van geselecteerde velden; onze code voegt onder andere creatie, verwijdering, de uitvoerende leiding en uitgewerkte referenties toe. Die functionele verschillen moeten behouden blijven.

## Code die beter gestructureerd moet worden

### Services in plaats van overal globale toegang

De agenda geeft het goede voorbeeld: `AgendaFetcher` ontvangt HTTP-client, State, configuratie, tijd, logger en cache-invalidatie via de constructor. Daardoor zijn de afhankelijkheden duidelijk.

`PublicPhotos`, `SubmissionGate`, `ApiAccess` en `LeadershipLog` gebruiken veel statische methoden en `\Drupal::...`. Dat werkt, maar koppelt ze sterk aan een volledig opgestarte site. Maak er gerichte services van en injecteer die in formulieren en controllers. Een pure hulpmethode voor datum- of tekstberekening mag statisch blijven. Procedurele hooks hoeven niet allemaal tegelijk herschreven te worden. Dit sluit aan bij de [Drupal-richtlijn voor dependency injection](https://www.drupal.org/docs/drupal-apis/services-and-dependency-injection/services-and-dependency-injection-in-drupal).

### De nieuwsbriefinzending doet te veel in één formulierklasse

[SubmissionForm.php](../web/modules/custom/ksa_newsletter/src/Form/SubmissionForm.php) bouwt de UI, controleert toegang, controleert quota, valideert foto's, maakt bestanden en Media, slaat het artikel op en ruimt fouten op.

Splits vooral opslag en publicatiebeleid af van de Form API-code. Gebruik gedeelde validatieregels waar dezelfde eis voor publieke inzendingen én beheer geldt. De openbare upload controleert bijvoorbeeld ook pixelafmetingen; de meegeleverde Media-veldconfiguratie heeft geen maximale resolutie ingesteld.

De bewuste keuze om geen anoniem AJAX-uploadendpoint aan te bieden is niet overbodig. Eerst de toegang en quota controleren en pas daarna bestanden bewaren blijft een nuttige grens.

### Leesbaarheid en vertaalbaarheid

Veel nieuwsbriefklassen staan vol lange éénregelige arrays, `if`-blokken en meerdere instructies per regel. Splits die op, gebruik imports en voeg typeverklaringen toe waar de parent-interface dat toelaat. Vertaalbare interfacezinnen horen door `t()`, `TranslatableMarkup` of Twig `|t` te gaan, ook op een Nederlandstalige site. Zo kunnen beheerders ze via interfacevertaling aanpassen.

Grote HTML-strings, bijvoorbeeld in het logboek, horen in een Twig-template met expliciete variabelen. Render arrays blijven geschikt voor formulieronderdelen en links.

### Rechten zijn deels gekoppeld aan een rolnaam

`ksa_editor_is_editor()` en `LeadershipLog::record()` controleren letterlijk `content_editor`. Dat is een projectkeuze, geen noodzaak van Drupal. Een tweede redactierol met dezelfde functionele rechten krijgt niet automatisch dezelfde beperkte interface of logging.

Gebruik voor de beperkte beheerervaring een expliciete permissie. Maak voor logregistratie apart duidelijk welke rollen of bewerkingen worden gevolgd. Gebruik geen zeer breed recht zoals `administer users` als enige semantische aanduiding voor “leidinglogboek bekijken”.

## Concrete opruimkandidaten

| Plaats | Bevinding | Veilige vervolgstap |
|---|---|---|
| `ksa_newsletter/css/newsletter.css` | `.newsletter-admin-filter` komt nergens meer voor als gegenereerde klasse in eigen PHP/Twig. `.newsletter-text` is eveneens een restant van de oude detailweergave; er zijn ook conflicterende `white-space`-regels. | Na controle van actieve weergaven verwijderen; gedeelde CKEditor-selectors behouden. |
| `ApiController::articles()` | `$body` wordt ingelezen en daarna niet gebruikt. | Verwijderen. |
| `PublishPhotosForm.php` | `use Drupal\Core\Url` wordt niet gebruikt. | Import verwijderen. |
| `NewsletterArticle.body` en `SubmissionForm` | Oud `body`-veld wordt bewaard én bij nieuwe inzendingen nog gevuld naast `content`. De API leest primair `content`. | Tijdelijk bewaren voor migratie; stop dubbele writes na controle van afhankelijkheden. Veld pas in aparte migratie verwijderen. |
| `Settings::get()` | Standaardwaarden staan zowel in PHP als in `config/install/ksa_newsletter.settings.yml`. | Na betrouwbare installatie/updateprocedure één gezaghebbende bron kiezen of gelijkheid testen. |
| Toolbar-modules | `admin_toolbar`, `admin_toolbar_tools` en `gin_toolbar` zijn lokaal actief naast core Navigation. De projectnotities melden dat ze daar niets renderen. | Eerst afhankelijkheden en werkelijk gebruik controleren; vervolgens via beheer de overbodige modules verwijderen. |
| Oud bloktype `agenda` | Bestaat nog met onder meer oude agenda/API-velden; lokaal 0 opgeslagen blokken van dit type. De actieve agenda gebruikt een Block-plugin. | Ook Layout Builder-revisies en configuratiereferenties onderzoeken vóór verwijderen. |
| `simplify.global.yml` en `ksa_editor_form_alter()` | Verbergen deels dezelfde metadata. | Per formuliersoort één eigenaar kiezen; beveiligingschecks niet weghalen. |
| README en oudere docs | README beschrijft de nieuwsbrief en logging niet volledig en bevat verouderde deploymenttekst. Oudere pagina's noemen vervangen assets of widgets. | Huidige architectuur bijwerken; historische scans herkenbaar als historisch bewaren. |

**Niet blind verwijderen:** `ksa-header.png` wordt nog gebruikt in Metatag-configuratie, ook al gebruikt de zichtbare achtergrond WebP. `inter-var.woff2` is de gedocumenteerde bron voor het kleinere Latijnse font. Dat zijn geen bewezen ongebruikte bestanden.

## Thema en interactie

De SDC-opzet is passend: Drupal levert inhoud, de componenten tekenen kaarten, menu, hero en footer. De kleine wrappers in `templates/` zijn de verbinding tussen Drupal en de componenten en zijn niet automatisch duplicatie.

Er zijn wel kwetsbare koppelingen:

- `ksa.theme` en de ploeg-/leiderdetailtemplates gebruiken node-ID **33** voor het leidingoverzicht; het headeractiemenu gebruikt **35** voor inschrijven. Dat werkt in de huidige database maar niet betrouwbaar na opnieuw opgebouwde inhoud. Gebruik beheerbare interne verwijzingen in themaconfiguratie of een andere expliciete bron.
- De contactgrid trekt meerdere Layout Builder-wrappers samen met lange `:has(...)`-selectors en `display: contents`. Dat lost de ontbrekende Plus16-tegel op, maar hangt sterk aan de HTML-structuur. Een expliciete sectiestijl of gericht layout-template is beter beheersbaar.
- `card.css` bevat opeenvolgende aanvullingen die eerdere regels bijsturen. Consolidatie per variant maakt de uiteindelijke afmetingen duidelijker, zonder het ontwerp te veranderen.
- `jos-login.js` blijft `requestAnimationFrame` aanvragen, ook wanneer de pose is uitgestabiliseerd. Bij minder beweging wordt de interpolatie aangepast, maar de loop blijft lopen. Maak beweging gebeurtenisgestuurd en pauzeer wanneer ze niet nodig is.
- `media-strip.js` kopieert DOM-subtrees zonder de ID's en contextual controls op te ruimen zoals `hero.js` dat wel doet. Dat kan bij uitgebreidere Media-markup dubbele ID's of dubbele bewerkbediening opleveren. Dit is een codepad om gericht te testen; geen nieuw visueel vastgesteld defect.
- De nieuwsbriefcontroller en het agendablok verwijzen rechtstreeks naar `ksa:`-componenten. Het werkt voor dit project, maar modules zijn daardoor afhankelijk van dit thema. Een theme hook/template als scheiding maakt later wisselen eenvoudiger.

De mascottecode is relatief groot: `jos.css` alleen al telt 648 regels. Dat is hoofdzakelijk de uitwerking van gevraagde animaties. Een Drupal-module installeren gaat die specifieke bewegingen niet vanzelf vervangen. Minder scenario's is een productkeuze; betere opdeling en minder onnodige frames zijn technische verbeteringen.

## Maatwerk dat we moeten behouden

| Onderdeel | Waarom het er is |
|---|---|
| `EditorLayoutAccess` en `EditorRoutes` | Verhinderen dat leiding via directe routes toch de paginaopbouw verandert. Alleen knoppen verbergen is onvoldoende. |
| `SubmissionGate` | Een gedeelde instuurlink geeft schrijftoegang zonder lees- of beheerrechten. Vernieuwen trekt bestaande sessies in. |
| Eigen beperkte nieuwsbrief-API | Levert precies het overeengekomen gegevensmodel aan een lokale app, inclusief beveiligde en eventueel openbare foto-URL's. Algemene JSON:API is niet per definitie eenvoudiger voor deze eis. |
| Expliciete fotopublicatie | Houdt originelen privé en geeft geselecteerde kopieën unieke vaste URL's. |
| Agendafeed met bewaarde laatste geslaagde gegevens | Voorkomt dat een tijdelijke Google-storing alle activiteiten laat verdwijnen. |
| Dagafhankelijke agenda-cacheverversing | Laat voorbijgaande activiteiten verdwijnen, ook als een volledig gecachte pagina anders niet opnieuw opgebouwd wordt. |
| Kleine SEO-module | Corrigeert canonicals en ongeldige paginanummers van het nieuwsarchief; de rest is al Metatag, Redirect, Pathauto en Simple Sitemap. |

Een eigen content entity zoals `newsletter_article` is óók Drupal-standaardontwikkeling. Het probleem is niet dat er een eigen entiteit bestaat. De vraag is of die de standaard opslag, velden, formulieren, Views, rechten en revisies gebruikt. Dat doet deze entiteit inmiddels grotendeels.

## Testen en beheerbaarheid verbeteren

De 14 scripts zijn waardevolle regressiecontroles. Er is in deze repository geen eigen PHPUnit-configuratie of GitHub Actions-workflow gevonden. Sommige scripts verwachten bestaande node-ID's en fixtures. Andere wisselen tijdelijk API-State of schrijven bestanden. Dat beperkt herhaalbaarheid en maakt ze minder geschikt voor willekeurige omgevingen.

Zet de logica geleidelijk om naar:

- Unit-tests voor kalender-ID's, datums en ICS.
- Kernel-tests voor entiteiten, revisies, opslag, publicatiekoppelingen en toegangsbeleid.
- Functional-tests voor echte route- en formulieraanvragen.
- FunctionalJavascript-tests voor Media Library, Layout Builder en CKEditor.

Dit zijn de [standaard testlagen van Drupal](https://www.drupal.org/docs/develop/automated-testing/types-of-tests). Voeg ze gericht toe rond de risicovolle refactors. Een volledige herschrijving van alle bestaande tests is geen voorwaarde om de eerste verbetering te maken.

## Aanbevolen volgorde

1. Configuratie en herstelprocedure betrouwbaar maken; beide omgevingen vergelijken vóór export/import.
2. Logdekking en bescherming van nieuwsbriefpublicaties aanvullen, met regressietests.
3. Leidinglijsten naar Views brengen en de bevestigingen naar standaard bevestigingsformulieren.
4. Nieuwsbriefservices opsplitsen, dependencies injecteren en PHP leesbaar formatteren.
5. Logquery en filter naar Views, presentatie uit de controller halen.
6. Oude code/configuratie gericht verwijderen, vaste node-ID's vervangen en CSS consolideren.
7. De tests in een herhaalbare ontwikkel- en CI-werkwijze opnemen.

Per stap: eerst huidige rechten en uitvoer vastleggen, één afgebakende wijziging, dezelfde controles uitvoeren, vervolgens pas naar test. Geen nieuw ontwerp nodig voor deze opschoning.
