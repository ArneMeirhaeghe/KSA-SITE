# Audit gebruiksgemak en SEO: uitvoering — 16 en 17 november 2026

Dit hoort bij [audit-gebruiksgemak-seo-2026-11-16.md](audit-gebruiksgemak-seo-2026-11-16.md).
Daar staat wat er mis was. Hier staat wat er is gedaan, en wat nog van jou is.

Alles is **lokaal** uitgevoerd op `ksa.ddev.site`.
De code staat sinds 23 november 2026 in negen lokale commits op `main`, na akkoord van Arne.
Niets is gepusht of uitgerold. Er is geen configuratie geëxporteerd.

Terug naar vroeger kan met een snapshot:

| Snapshot | Toestand |
|---|---|
| `voor-audit-fases-2026-11-16` | vóór alle wijzigingen |
| `voor-beeldstijlen-2026-11-16` | vóór het opruimen van de beeldstijlen |
| `voor-vertalingen-2026-11-16` | vóór het inladen van de Nederlandse vertalingen |
| `voor-core-11.4.13-2026-11-23` | vóór de update van 23 november |

```sh
ddev snapshot restore voor-audit-fases-2026-11-16
```

## In het kort

- Fase 0, 1 en 2 uit het plan zijn uitgevoerd. Twee stappen niet: zie "Niet gedaan".
- Daarbovenop: de beeldstijlen opgeruimd en het beheer in het Nederlands gezet.
- Alle controlescripts zijn groen.
- Een tweede reviewer las de code na. Geen kritieke fouten. De punten daaruit zijn verwerkt: zie "Nagelezen".
- De snelheid (PageSpeed) is **niet** opnieuw gemeten. Dat kan pas na een uitrol op de testsite.

## Wat is gedaan

**Code** staat in Git-bestanden. **UI** is geklikt in het beheer en staat in de database.

### Fase 0 — Veiligheid

| Stap | Wat | Soort | Resultaat |
|---|---|---|---|
| 0.1 | Core 11.4.10 → 11.4.12, Single Content Sync 1.4.19, View Unpublished 1.5.0, Symfony 7.4.23 | composer + `update.php` | `composer audit`: 16 adviezen → 0 |

### Fase 1 — Techniek

| Stap | Wat | Soort | Resultaat |
|---|---|---|---|
| 1.1 | De pagina verspringt niet meer bij het laden | code (CSS) | opmaak vóór en na JavaScript is gelijk op 375 en 1440 px |
| 1.2 | 51 redirects | UI | 51 op 51: één 301, doel geeft 200 |
| 1.3 | Leiders uit de sitemap en `noindex`; `/home` eruit; `/node` weg | UI | sitemap 63 → 31 adressen; `/node` geeft 404 |
| 1.4 | Sitemap in `robots.txt` | composer | regel staat er en overleeft een core-update |
| 1.5 | Lichte deelfoto (JPEG) voor WhatsApp en Facebook | UI | zwaarste deelfoto 3343 → 265 KB |
| 1.6 | Betere titels voor Google | UI + code | zie tabel hieronder |
| 1.7 | Eigen beeldstijlen voor kaarten | UI | Leiding 1295 → 403 KB, home 241 → 135 KB aan kaartfoto's |
| 1.8 | Lettertype en hero vooraf laden | code | home 2 preloads, andere pagina's 1 |

Titels:

| Pagina | Was | Nu |
|---|---|---|
| `/leiding` | Leiding | Leiding en leeftijdsgroepen |
| `/rondes` | Rondes | Rondes, activiteiten en kampen |
| `/ons-verhaal` | Ons verhaal | Over onze jeugdbeweging |
| `/nieuws` | Nieuws — pagina 1 | Nieuws |
| `/ploegen/knim` | Knim | Knim: 1ste en 2de middelbaar |

De koppen op de pagina zelf (H1) zijn niet veranderd.

### Fase 2 — Gebruiksgemak

| Stap | Wat | Soort | Resultaat |
|---|---|---|---|
| 2.1 | Ploegpagina toont plaats, leeftijd en beschrijving | UI + code | 28–47 woorden → 53–78 woorden |
| 2.2 | Banner "Welke groep past bij mijn kind?" op de startpagina | UI | van home naar de juiste ploeg in twee klikken |
| 2.3 | Knop "Inschrijven" naast het menu op gsm; voettekstlinks 17 → 24 px hoog | code | de knop staat er vanaf een gsm van 360 px; op 320 px valt hij weg, zodat niets overloopt |
| 2.4 | Witte tekst op de foto is leesbaar | code | hero-intro 4,15 → 4,9; voettekst 2,97 → minstens 4,8 (norm 4,5) |
| 2.5 | Google-kalender laadt pas na een klik | code | geen Google-verzoeken meer bij het openen van Rondes |
| 2.7 | "Nieuws" in de voettekst | UI | onder "Volg ons"; de voettekst blijft even hoog |

### Extra, gevraagd tijdens het werk

| Wat | Soort | Resultaat |
|---|---|---|
| Beeldstijlen opgeruimd en hernoemd | UI | 19 → 15 stijlen en 5 → 2 responsive stijlen; de naam zegt waar de stijl voor dient. Zie [beeldstijlen.md](beeldstijlen.md) |
| Beheer in het Nederlands | UI | 2308 → 11 734 vertaalde teksten |
| Views-blokken met een duidelijke naam | UI | "Ploegkaarten (overzicht Leiding)" in plaats van "Ploegen: Block" |
| "Basic page" heet nu **Basispagina**; menu "Footer" heet **Voettekst** | UI | met uitleg in het Nederlands |
| SEO-titel per pagina is weer te bewerken door de beheerder | UI | Simplify verborg het veld voor iedereen |
| Week start op maandag, land is België | UI | datumkiezers kloppen |

## Nagekomen: beveiligingsupdate van 23 november 2026

Op 18 november bracht Drupal twee nieuwe beveiligingsadviezen uit voor core:
SA-CORE-2026-017 (zeer kritiek: code uitvoeren op de server) en SA-CORE-2026-018.
Ze treffen elke versie onder 11.4.13. De update van 16 november (11.4.12) volstond dus niet meer.

Op 23 november bijgewerkt, na akkoord van Arne:

- Core 11.4.12 → 11.4.13. Vier afhankelijkheden gingen mee: Guzzle (drie pakketten) en de e-mailvalidatie.
- Geen database-updates nodig. Alleen `composer.lock` wijzigde.
- `composer audit` meldt opnieuw niets. De rode melding in het beheer is weg.
- Alle scripts opnieuw gedraaid: SEO 405 op 405, rechten 419 op 419, agenda, logboek en nieuwsbrief groen.

De testsite en productie hebben dezelfde update nodig.

## Waarom het beheer Engels was

De map `web/sites/default/files/translations` bestond niet.
Drupal kon daardoor nooit vertalingen ophalen. Er zaten maar 893 teksten van core in, van de ruim 10 000.

Ik maakte de map aan en klikte **Rapporten → Beschikbare nieuwe vertalingen → Vertalingen bijwerken**.

Gevolgen:

- 9426 teksten toegevoegd. Layout Builder, menu's en de mediabibliotheek zijn nu Nederlands.
- 81 configuratie-objecten kregen een Nederlands label. Bijvoorbeeld rollen, tekstformaten en beheer-overzichten.
- De accountmails (wachtwoord vergeten) zijn nu Nederlands.
- Op de publieke pagina's veranderde **niets**. Dat is nagekeken met een crawl vóór en na.

Eén rare vertaling: in de teksteditor heet de codetaal "Ruby" nu "Robijnrood". Onschuldig.

## Afwijkingen van Figma — goedgekeurd op 23 november 2026

Arne keurde deze vijf afwijkingen goed op 23 november 2026.
Ze waren toen al gebouwd, omdat de vraag was om niet te stoppen.

### 1. Donkerdere laag op de hero en de voettekst

- **Wat het is:** de donkere laag over de illustratie, zodat witte tekst leesbaar is.
- **Waarom:** de norm is 4,5 op 1. Gemeten was 2,97 tot 4,15.

| Wat | Waarde | Was | Bron |
|---|---|---|---|
| `--color-hero-overlay` (nieuw) | `rgb(30 30 30 / 30%)` | 20% (`--color-image-overlay`) | gemeten contrast |
| `--footer-overlay` (nieuw) | verloop 35% → 60% | 20% | gemeten contrast |
| `--color-hero-base` (gewijzigd) | `#287474` | `#5fa95b` | kleur die het dichtst bij de illustratie ligt, voor het moment vóór het beeld laadt |

Beslist op 23 november: de laag mag donkerder dan de 20% uit Figma. Zie ook [design-tokens.md](design-tokens.md).

### 2. Knop "Inschrijven" in de mobiele kop

- **Wat het is:** de groene knop staat op gsm naast het menu-icoon.
- **Hergebruik:** de bestaande knop uit het menu Headeracties. Geen nieuwe stijl.
- **Nieuwe tokens:** geen.
- Hij verschijnt alleen als de rij 296 px of breder is. Dat is de startpagina op een gsm van 360 px.
- De drempel past bij het woord "Inschrijven". Een langere knoptekst vraagt een nieuwe drempel. Zie [header.md](header.md).

### 3. Vak "Toon maandkalender"

- **Wat het is:** een kader met één zin en een knop, op de plaats van de Google-kalender.
- **Hergebruik:** `.agenda__subscribe-button` en `.agenda__empty` uit `agenda.css`.
- **Waarden:** rand `--color-border`, hoek `--radius-card-image`, 24 px ruimte zoals de agendakaarten.
- **Nieuwe tokens:** geen.

### 4. Plaats, leeftijd en beschrijving op de ploegpagina

- Zie [ploeg-detail.md](ploeg-detail.md), "Afwijking van Figma".
- Dit draait een eerdere keuze terug: die drie velden waren bewust uit de layout gehaald.
- **Nieuwe tokens:** geen.

### 5. Banner "Welke groep past bij mijn kind?"

- **Wat het is:** het bestaande bloktype **Tekst met beeld**, stijl Compact. Dezelfde banner als op Verhuur.
- **Waar:** startpagina, tussen Nieuws en Wekelijkse rondes.
- **Nieuwe code:** geen. De leiding kan tekst en knop zelf aanpassen.
- De tekst schreef ik op basis van de ploeggegevens. Laat de leiding hem nalezen.
- Het eerste idee was de zeven ploegkaarten op home. Dat vroeg een nieuw onderdeel en maakte de pagina zwaar.

## Over te zetten naar test en productie

De config-export doe jij, zoals in de README staat. Dit is de lijst om bewust over te nemen.

### Configuratie van deze audit

| Onderwerp | Objecten |
|---|---|
| Beeldstijlen, nieuw | `image.style.card`, `image.style.card_fit`, `image.style.social_share` |
| Beeldstijlen, nieuwe naam | `image.style.wide`, `large`, `hero_background`, `hero_background_md`, `hero_background_sm` |
| Beeldstijlen, **verwijderen** uit `config/sync` | `image.style.article_wide`, `article_wide_md`, `article_wide_sm`, `max_325x325`, `max_650x650`, `max_1300x1300`, `max_2600x2600`; `responsive_image.styles.article_wide`, `narrow`, `wide` |
| Kaarten | `core.entity_view_display.node.article.teaser`, `node.team.teaser` |
| Ploegpagina | `core.entity_view_display.node.team.default` |
| Delen en titels | `metatag.metatag_defaults.global`, `node__article`, `node__team`, `node__leader` |
| Sitemap | `simple_sitemap.bundle_settings.default.node.leader` |
| `/node` weg | `views.view.frontpage` |
| Bloknamen | `views.view.teams`, `views.view.leaders`, `views.view.news` |
| Namen | `node.type.page`, `system.menu.footer` |
| SEO-veld zichtbaar | `simplify.global` |
| Week en land | `system.date` |

Bij het opslaan van een formulier maakt Drupal soms een leeg bestand aan. Onschuldig, wel meenemen:
`simplify.content_type.page`, `simplify.content_type.leader`,
`language.content_settings.node.page`, `language.content_settings.node.leader`.

### Configuratie door de vertalingen

Drupal meldde 81 objecten waar een label of tekst Nederlands werd.
Bijvoorbeeld `user.role.*`, `filter.format.*`, `system.action.*`, `core.date_format.*`, `user.mail`
en de beheer-overzichten (`views.view.content`, `media`, `files`, …).

Makkelijkst: op de andere omgeving ook **Vertalingen bijwerken** klikken. Dan ontstaan dezelfde labels.

### Wat al vóór deze audit niet geëxporteerd was

`config/sync` liep al achter op de database. Niet van deze audit:

- alles van de nieuwsbrief (`ksa_newsletter.settings`, `filter.format.newsletter`, `editor.editor.newsletter`, `media.type.newsletter_photo` met veld, `views.view.newsletter_articles`, rechten in `user.role.*`);
- `honeypot.settings`, `single_content_sync.settings`, `system.action.content_bulk_export`, drie regels in `core.extension`;
- `core.entity_view_display.media.image.article_wide`.

### Inhoud (database, geen configuratie)

- 51 redirects. Zie [redirects-livegang-2026-11-05.md](redirects-livegang-2026-11-05.md).
- Startpagina (node 39): banner in de layout, en uitgesloten uit de sitemap.
- Titel voor Google op node 33, 34 en 36.
- Menulink "Nieuws" in de voettekst.
- De ingeladen vertalingen.

Draai na het overzetten `drush cr`. Anders blijven oude doorverwijzingen en deeltags in de cache.

## Beslissingen die ik zelf nam

| Beslissing | Waarom | Kost als het fout is |
|---|---|---|
| Pas gecommit na jouw akkoord (23 november), geen aparte werkmap | jouw afspraak: Git-wijzigingen stel ik voor | de negen commits staan alleen lokaal en zijn terug te draaien |
| Keuzes 1 tot 4 uit het rapport volgens mijn voorstel | je zei "doe alle fasen" | vier afgebakende wijzigingen terugdraaien |
| Single Content Sync blijft aan | het is jouw manier om inhoud over te zetten | module blijft aan tot jij beslist |
| `blog?page=N` gaat naar `/nieuws?page=0` | met doel `/nieuws` werd het toch een 404 | vier redirects aanpassen |
| Geen responsive stijl voor kaarten | de opmaak Miniatuur kan dat niet | PageSpeed meldt op desktop nog wat beeldwinst |
| Banner in plaats van ploegkaarten op home | bestaand onderdeel, lichter, geen Hoofdleiding ertussen | Views-blok Ploegkaarten toevoegen in Layout Builder |
| Plaats, leeftijd en beschrijving terug op de ploegpagina | de titel voor Google belooft de leeftijd | drie blokken verwijderen in Layout Builder |
| "Nieuws" onder "Volg ons" | de voettekst blijft even hoog | de link verplaatsen in het menu |
| Mappen `files/sync` en `files/translations` aangemaakt | `update.php` en de vertalingen blokkeerden erop | twee lege mappen verwijderen |
| Nederlandse vertalingen ingeladen | het beheer moet Nederlands zijn | snapshot `voor-vertalingen-2026-11-16` terugzetten |
| De node-nummers 33 en 35 blijven in de code | bewuste keuze in het thema | zie de controlelijst |
| Metatag 2.2.7 en Pathauto 1.16 niet geïnstalleerd | geen beveiligingsupdate, vlak voor de livegang | kan na de livegang |

Drie regels botsten:

- De ontwerpregel "nieuw token: eerst voorstellen en wachten" tegenover jouw vraag om niet te stoppen.
  Ik heb doorgewerkt en de voorstellen hierboven gezet. Op 23 november zijn ze goedgekeurd.
- De werkwijze "aparte werkmap en commit per taak" tegenover jouw afspraak over Git. Jouw afspraak ging voor.
- "Geen assistent-bestanden in Git" tegenover de proef met een gedeelde werkwijze in `.claude/skills/`.
  Met `.claude/` in `.gitignore` kan die map niet meer gedeeld worden via Git. Jouw vraag ging voor. Beslis of dat zo blijft.

## Nagelezen

Een tweede reviewer las op 17 november alle codewijzigingen, zonder mijn uitleg erbij.
Uitkomst: geen kritieke fouten, twee belangrijke punten en een reeks kleine.

Verwerkt, telkens met een test vooraf:

| Punt | Wat er mis was | Nu |
|---|---|---|
| Menu op gsm | Startte het script niet (geblokkeerd of mislukt), dan was het menu weg en deed het menu-icoon niets | het menu komt na 6 seconden terug onder het logo |
| Stappen en tabbladen | De CSS verborg elk tweede blok in zo'n sectie, ook een blok dat geen stap is | alleen blokken met stijl Stap of Tab; de vrijgehouden ruimte verdwijnt ook in het vangnet |
| Titel van het nieuwsarchief | "Nieuws-archief — pagina 1" werd "Nieuws" | het patroon vraagt een spatie voor en na het streepje |
| Controle van redirects | Een redirect naar een andere website kon ten onrechte slagen | alleen doelen op de eigen site worden gecontroleerd |
| Kalenderknop | Cmd-klik of Ctrl-klik opende geen nieuw tabblad | werkt weer als een gewone link |
| Ploegbeschrijving | Twee alinea's plakten aan elkaar | ruimte tussen alinea's |
| `robots.txt` | Een dubbele lege regel | één lege regel |

Bij het nameten kwam nog iets boven: op een gsm van 360 px stond de knop Inschrijven nét niet op de startpagina.
De drempel is daarom van 300 naar 296 px gegaan.

Bekend en niet aangepast:

- De rij met tabbladen en de knoppen Vorige en Volgende komen van het script. Wat eronder staat, schuift daardoor nog een beetje. Geschat effect: rond 0,01, ruim onder de grens van 0,1.
- "Toon maandkalender" is een link die zich als knop gedraagt. Een schermlezer zegt "link".
- Na een klik op de paginering van de agenda moet je de kalender opnieuw openen.
- De tabvolgorde in de mobiele kop: menu-icoon, logo, Inschrijven.
- Breekt "plaats · leeftijd" op een breed scherm af, dan begint de tweede regel met het puntje.
- De sitemap-regel in `robots.txt` noemt `https://ksadeinze.be`. Antwoordt productie ook op `www`, stuur dat dan door.

`assets/robots-additions.txt` zit mee in de commit met de updates.
Zonder dat bestand stopt `composer install` op de server met een fout.

## Niet gedaan

| Wat | Waarom |
|---|---|
| Stap 0.2: Single Content Sync uitschakelen | jouw keuze, op productie |
| Stap 2.6: teksten en privacypagina | inhoud verzin ik niet; dat is voor de leiding |
| Fase 3 en 4 | horen bij de livegang en erna |
| PageSpeed opnieuw | kan pas na een uitrol op de testsite |
| Test op een echte gsm, voorbeeld in WhatsApp | kan ik niet |
| L4, L6, S6, S8, G8, I2 | stonden onder "later, zonder haast" |

## Controles

Gedraaid op 17 november, na de laatste wijziging.

| Controle | Uitkomst |
|---|---|
| `scripts/check-seo.php` | 405 controles, 0 fouten (was 237 vóór de audit) |
| `scripts/check-editor-access.php` | 419 op 419 |
| Agenda (5 scripts) | 17/17, 7/7, 7/7, 19/19, OK |
| `scripts/check-leadership-log.php` | 35 geslaagd |
| Nieuwsbrief (5 scripts) | 33, 23, 24, 13 en 57 geslaagd |
| Crawl van de hele site | 61 adressen, allemaal 200; 106 afbeeldingen, allemaal 200 |
| Externe links | 8 op 8 antwoorden |
| `composer audit` | geen adviezen |
| Statusrapport van Drupal | geen fouten, vijf waarschuwingen (zie hieronder) |
| Console van de browser | geen fouten of waarschuwingen op home, Inschrijving en Rondes |

`check-seo.php` schrijft bij elke run over `docs/qa/seo-after-2026-10-07.json` heen.
Dat bestand is telkens teruggezet. Het is dus niet gewijzigd.

Nieuw in `check-seo.php`: de redirects eindigen op een bestaand adres, `/node` is weg,
leiders zijn `noindex`, de nieuwe titels, de lichte deelfoto, de sitemap-regel,
en plaats, leeftijd en beschrijving op de ploegpagina.

## Controlelijst vóór de livegang

Zelf bedacht en lokaal nagekeken waar dat kon. De lijst onder "H" in het auditrapport blijft gelden.

### Moet eerst

| Punt | Wat ik lokaal zag | Wat te doen |
|---|---|---|
| `web/sites/default/files.zip` | 48 MB, **voor iedereen te downloaden** op `/sites/default/files.zip` | niet mee uitrollen; verwijderen of buiten `web/` zetten |
| Losse mappen in `web/sites/default/` | `css`, `js`, `php`, `styles`, `languages`, `sync`, `translations`: een verkeerd uitgepakte kopie van `files/` | niet committen, niet uitrollen; verwijderen |
| Testinhoud | 9 nieuwsbriefartikels met "TEST", 5 testfoto's waarvan 1 publiek, ploeg "+16" met "Testtekst" (niet gepubliceerd) | verwijderen vóór de livegang |
| Node-nummers | het thema verwacht Leiding op `/node/33` en Inschrijving op `/node/35` | op productie nakijken. Zie [navigatie.md](navigatie.md) |
| Cron | automatische cron staat uit (interval 0) | echte cron op de server, bv. elk kwartier. Nodig voor agenda en sitemap |
| Sitemap-domein | het basisadres van de sitemap is leeg | op productie invullen of cron met het juiste domein draaien; daarna opnieuw maken |
| `settings.php` | `trusted_host_patterns` staat op "alles" (DDEV); `config_sync_directory` is niet gezet | op productie: het echte domein, en de map `../config/sync` |
| Foutmeldingen | Drupal zelf staat goed (`hide`). DDEV toont wel PHP-fouten met paden | op de server `display_errors` uit |
| Afzender | het e-mailadres van de site is nog een persoonlijk adres | vervangen door een adres van KSA |
| Accounts | er is één account: `admin` | accounts voor de leiding aanmaken, sterk wachtwoord voor `admin` |

### Nakijken op de server

| Punt | Waarom |
|---|---|
| PHP met GD **en AVIF** | alle beeldstijlen maken AVIF. Zonder AVIF valt Drupal terug op WebP |
| Privémap buiten `web/` en schrijfbaar | de originele nieuwsbrieffoto's staan daar |
| Map `files/translations` bestaat | anders blijft het beheer daar Engels |
| Uitgaande mail werkt | wachtwoord vergeten en meldingen. Kijk ook naar SPF en DKIM van het domein |
| `settings.php` alleen-lezen | het statusrapport waarschuwt lokaal dat het schrijfbaar is |
| Browsercache (L5) | staat op 0. Kies bij de livegang bv. 15 minuten |
| `http` → `https`, HSTS, `www` | zie "H" in het auditrapport |
| `robots.txt` en `noindex` | de blokkade van de testsite mag niet mee naar productie |
| Back-up | database en `files/`, vóór en na de livegang |

### Daarna

| Punt | Waarom |
|---|---|
| PageSpeed op test en productie | bevestigt dat het verspringen weg is |
| Voorbeeld van een gedeelde link in WhatsApp | bevestigt de deelfoto |
| Search Console: domein, sitemap, 404's | redirects opvolgen |
| Toestemming voor foto's van minderjarigen | geldt voor de site en voor de 1194 foto's |
| Updates Metatag 2.2.7 en Pathauto 1.16 | geen beveiligingsupdates |

Waarschuwingen in het statusrapport, allemaal bekend:
`settings.php` is schrijfbaar, de instelling voor HTML5-validatie ontbreekt (voor Drupal 12),
Toolbar en Navigation staan samen aan, een overbodig filter door View Unpublished,
en de twee gewone updates.

## Voor de leiding

Dit kan alleen de leiding invullen. Het stond al in het rapport (G5, G9, I1):

- de actuele boekjes (boekje 2) en Kerstrozen 2026;
- activiteiten vóór 23 januari in de agenda;
- het lidgeld: de site zegt 37 euro, de oude site 35;
- een korte privacypagina;
- de tekst van de nieuwe banner nalezen.

## Onzekerheid

- Het verspringen is gemeten als "opmaak vóór en na JavaScript is gelijk". Dat is geen PageSpeed-score.
- Het vangnet na 6 seconden is alleen in Chrome getest. Het rekent op moderne CSS (`:has()`, container queries, `@media (scripting)`).
  Een oudere browser zonder die functies toont het menu en alle stappen meteen, zoals vroeger.
- Het contrast is nagerekend uit de illustratie en de laag. Op 2560 px breed haalt de hero-intro 4,52: nipt.
- De crawl en de scripts draaien lokaal. De server kan zich anders gedragen.
- Niet getest met een schermlezer of op een echte telefoon.
