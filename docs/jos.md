# Jos de vos in de hero

## Jos in de footer (6 oktober 2026)

Pagina's zonder bestaande Jos krijgen dezelfde animatie in een eigen strook
in de footer, met een pauze-/afspeelknop. Het dagschema en de voorkeur voor
minder beweging blijven gelden. Op mobiel blijft Jos op een vaste plek.

`site-footer.twig` bevat een inert template. `site-footer.js` voegt dit alleen
toe wanneer de hoofdinhoud geen hero-Jos, account-Jos of inschrijvingsstappen
met Jos heeft. Zo ontstaan geen dubbele SVG-ID's of dubbele animaties.
De footer gebruikt `data-jos-controller` om de bestaande regisseur te pauzeren.
Drupal detach ruimt de listeners en de ingeladen Jos op.

Gecontroleerd: alle 50 inhoudspagina's plus login en wachtwoordherstel hebben
precies één Jos-locatie. Home, Inschrijving en de twee accountpagina's hebben
geen footer-Jos. Contact op 320 px heeft geen overloop; pauzeren en hervatten
werken. JavaScript-syntax en `git diff --check` slagen. Minder beweging is
in CSS/JS voorzien, niet via een gewijzigde OS-instelling getest.
Bewijs: `qa/footer-jos-checks.json`, `qa/footer-jos-mobile.png` en
`qa/footer-jos-desktop.png`.

Status: gebouwd op 29 september 2026. Nog niet in de echte Drupal-site bekeken.
Jos staat niet in het Figma-ontwerp. Plaats en gedrag zijn keuzes van Arne.

## Wat het doet

Jos staat onderaan in de hero, vóór de fotokaarten.
Hij doet iets anders volgens het uur van de bezoeker.
Om de paar activiteiten wandelt hij naar de andere kant.
Een klik op Jos toont meteen iets anders.

| Tijd | Wat Jos doet |
|---|---|
| 07–11 u | vlaggengroet, zwaaien, rust, springen |
| 11–17 u | tent opzetten, springen, dansen, zwaaien |
| 17–22 u | kampvuur, dansen, rust |
| 22–07 u | slapen in zijn bedje, voor het clubhuis |

Vlaggengroet, kampvuur en tent gebeuren altijd links: daar is plaats voor de props.
Slapen gebeurt rechts, voor het clubhuis. De rest mag aan beide kanten.

### Slapen in zijn bedje

- Eerst verschijnt het bedje, daarna gaat Jos liggen: op zijn rug, hoofd op het kussen.
- Deken in KSA-groen, handen en staart eronder. Borst en deken ademen mee, Zzz boven zijn hoofd.
- Opstaan: Jos staat eerst recht, daarna verdwijnt het bedje.
- 's Nachts speelt alleen dit scenario. Het "boing"-effect slaat dan over, zodat het bed niet elke 15 s opveert.
- In `jos.svg`: `.jos-bed-back` (hoofdeinde, kussen) achter Jos en `.jos-bed-front` (matras, deken, voeteneinde) ervoor.
  `.jos-pose` omsluit het lijf en kantelt het in bed. Zzz blijven rechtop.
- Backup van voor het bedje: `backups/2026-09-30-voor-bedje/`.

## Bestanden

Alles zit in het SDC-component `web/themes/custom/ksa/components/jos/`.

| Bestand | Inhoud |
|---|---|
| `jos.svg` | De tekening. Eén bron voor Drupal én de demo. |
| `jos.twig` | Een knop met de SVG erin, via `source('@ksa/components/jos/jos.svg')`. |
| `jos.css` | Alle beweging. Eén blok per scenario: `.ksa-jos[data-scenario="…"]`. |
| `jos.js` | De regisseur: dagschema, wandelen, klikken, pauzeren. |

Aanpassingen aan de hero:

- `hero.twig`: één `include('ksa:jos')`, vóór de fotokaarten.
- `hero.css`: plaats en grootte van Jos, onderaan in het bestand.
- `hero.js`: de hero krijgt `.is-paused` als de pauzeknop aan staat.
  Met Jos blijft de pauzeknop ook bij één foto zichtbaar.

Kopie van de oude hero-bestanden: `backups/2026-09-29-voor-jos/`.

## Aanpassen

| Wat | Waar |
|---|---|
| Grootte van Jos | `--jos-width` in `hero.css` (desktop 170 px, gsm 104 px) |
| Linker- en rechterplek | `--jos-spot-left` en `--jos-spot-right` in `hero.css` |
| Dagschema | `PERIODS` bovenaan `jos.js` |
| Hoe lang een scenario duurt, en de kant | `SCENES` bovenaan `jos.js` |
| Wandelsnelheid | `WALK_SPEED` in `jos.js` (px per seconde) |
| Een beweging zelf | het scenarioblok in `jos.css` |

Een scenario in `jos.css` doet twee dingen:

1. Het zet variabelen: welke hand, mond en props zichtbaar zijn.
   Bijvoorbeeld `--r-fist: 1` toont de rechtervuist.
2. Het geeft lichaamsdelen een animatie.

Nieuw scenario? Maak een blok in `jos.css`, voeg de naam toe aan `SCENES`
en aan een lijst in `PERIODS`. Geen PHP of cache-instelling nodig,
wel `ddev drush cr` na een wijziging aan `jos.twig` of `jos.svg`.

## Gsm

Onder 700 px is Jos 104 px breed en staat hij vast rechts in de hoek.
Hij wandelt dan niet. Tent opzetten valt weg: die tent past daar niet.

## Toegankelijkheid

- Jos is een echte knop met het label "Laat Jos de vos iets anders doen".
- Alleen zijn getekende lichaam vangt klikken. Foto's eromheen blijven bruikbaar.
- De pauzeknop van de hero pauzeert ook Jos. Een verborgen tabblad pauzeert hem ook.
- Bij minder-bewegenvoorkeur staat Jos stil. Een klik toont wel een andere pose.
- De SVG zelf is `aria-hidden`. Alle id's en klassen beginnen met `jos-`,
  zodat ze niet botsen met andere SVG's op de pagina.

## Demo

`/prototypes/hero/` laadt dezelfde `jos.svg`, `jos.css` en `jos.js`.
Met **Tijd van de dag** test je elk moment zonder op de klok te wachten.
Via de URL kan het ook: `?tijd=ochtend`, `middag`, `avond` of `nacht`.
De demo haalt de SVG op met `fetch`: open hem via `https://ksa.ddev.site`,
niet als los bestand.

## Gecontroleerd (in de demo, Chromium)

- Alle vier de momenten van de dag, op 1280 px.
- Wandelen tussen links en rechts, en klikken op Jos.
- Pauzeknop pauzeert Jos en hervat hem. Bij één foto blijft de knop zichtbaar.
- Minder bewegen: geen animaties, een klik wisselt de pose.
- 402 en 320 px: Jos in de hoek, geen horizontale overloop.
- Geen fouten in de console. JavaScript gecontroleerd met `node --check`.

## Nog te doen

- `ddev drush cr` en de Home-pagina bekijken in de echte Drupal-site.
- Nakijken in Safari en Firefox (CSS nesting, `:is()`, `max()`).
- Layout Builder: Jos beweegt ook in de bewerkingsweergave. Nagaan of dat stoort.
