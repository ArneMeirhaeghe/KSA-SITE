# CLAUDE.md — Website KSA Deinze-Astene

## Wat dit is

Een nieuwe website voor **KSA Deinze-Astene**, een jeugdbeweging.
Het ontwerp staat volledig in Figma. Wij bouwen het na in Drupal.

De leiding beheert de site later zelf. Zij kunnen geen code.
Alles moet dus klikbaar zijn, en in het Nederlands.

## 🔴 Regel nummer één: de admin-UI doe je in de browser

Kan een taak via de Drupal admin-UI? **Dan doe je hem via Claude in Chrome.**
Niet via drush. Arne wil zien waarop er geklikt wordt.

Dit geldt voor onder meer:

- Content types, velden en weergaven aanmaken of wijzigen.
- Layout Builder-secties en blokken plaatsen.
- Menu's, blokken en views instellen.
- Theme-instellingen, permissies en rollen.
- Content en media toevoegen.

**Hoe:**

1. `ddev drush uli` voor een inloglink. Dat mag wél via de terminal.
2. Open die link met `navigate` in een nieuwe tab.
3. Klik stap voor stap. Neem een screenshot bij elke stap.
4. Meerdere stappen? Neem een **GIF** op met `gif_creator`, en geef hem
   een duidelijke naam, bijvoorbeeld `content-type-activiteit.gif`.

**Wanneer wél de terminal:**

- `ddev` zelf: start, stop, composer.
- Modules ophalen met `ddev composer require`.
- Recipes toepassen, cache rebuild, inloglink.
- **Nalezen** of iets klopte: `ddev drush config:get ...` als bewijs achteraf.

Kort: **bouwen doe je in de browser, controleren mag in de terminal.**

Twijfel je of iets via de UI kan? Ga er van uit dat het kan. Vraag het anders.

## Stack

- **Drupal 11.4.7**, map `drupal-ksa`, docroot `web`.
- **DDEV**, projectnaam `ksa`, PHP 8.3, MariaDB 10.11.
- **Gin 5** als admin-theme. KSA is het standaard front-endthema.
- **Layout Builder** aan op Basic page, ook per pagina aanpasbaar.
- Core **Navigation** vervangt de oude toolbar.

Alles draait via `ddev`. Nooit via `composer` of `php` op de Mac zelf.
De Mac draait PHP 8.5, en Drupal 11 wil dat niet.

```bash
ddev start
ddev composer require drupal/<module>
ddev drush en <module> -y
ddev drush cr
ddev drush uli            # inloglink
```

Site: https://ksa.ddev.site — login `admin`.

## Figma

https://www.figma.com/design/fjlPw9tE1afoSAoD7PBjy0/KSA-Deinze-Astene

Lees de Figma uit met de Figma MCP-tools. Niet gokken op waarden.
Gebruik `get_variable_defs` voor tokens, `get_screenshot` om te kijken.
Bij de MCP-limiet heeft Arne expliciet Figma Web toegestaan (28-09-2026).
Lees dan de echte eigenschappen in de browser; geen maten uit pixels schatten.

### Design tokens (uit Figma variables)

| Token | Waarde | Gebruik |
|---|---|---|
| Primary | `#54C44E` | knoppen, links, accent (fel groen) |
| Dark | `#1E1E1E` | tekst |
| Grey | `#9E9E9E` | bijschriften, datums |
| Light Grey | `#D9D9D9` | randen, scheidingslijnen |

Hero en footer gebruiken de afbeelding `Ksa_header`, met `#1E1E1E` op
20% dekking erboven. De hero heeft ook `#5FA95B` als onderliggende kleur.
Gemeten via Figma Web; details staan in `docs/design-tokens.md`.

### Typografie

Font: **Inter**, overal.

| Stijl | Grootte | Gewicht | Regelhoogte |
|---|---|---|---|
| h1 | 64px | 600 | Auto |
| h2 | 48px | 600 | 64px |
| h3 | 36px | 600 | Auto |
| h4 | 32px | 600 | Auto |
| h5 | 24px | 600 | 24px |
| h6 | 20px | 600 | Auto |
| p | 16px | 400 | 24px |
| small | 12px | 400 | Auto |

Gecontroleerd in de Figma-stijlen op 28 september 2026. `Auto` is niet
hetzelfde als `100%`. Verdere tokeninventaris: `docs/design-tokens.md`.

Ontwerpbreedte is 1280px. Content-kolom is 1080px breed.

### Hoofdmenu (uit de Navbar-component)

In deze volgorde:

`Leiding` · `Rondes` · `Inschrijving & info` · `Foto's` · `Ons verhaal` · `Verhuur`

Daarnaast twee knoppen, rechts:

- **Contacteer ons** — witte rand, transparant, radius 4px.
- **Inschrijven** — gevuld `#54C44E`, radius 4px.

Menu-tekst is Inter regular, 12px, wit. Het logo links is 129 x 40px.
De balk ligt over de hero, dus alles is wit.

### Pagina's in het ontwerp

1. Homepage — hero met foto's, daarna nieuwsberichten in een raster van 3.
2. Blog Detail — één nieuwsbericht.
3. Ons verhaal
4. Leiding — overzicht.
5. Leiding Detail — één leider.
6. Activiteiten & Kampen
7. Inschrijving & Praktische informatie
8. Foto's
9. Verhuur
10. Contact

Sommige pagina's staan er in meerdere versies. Vraag welke de juiste is
voor je begint te bouwen.

## Wat de site moet kunnen

- Info-pagina's en nieuws.
- Kalender en activiteiten.
- Foto-albums. Er staan al **1194 JPG's** in `../fotos`.

Inschrijven en ledenbeheer zijn **buiten scope** tot Arne anders zegt.

## Content model

Goedgekeurd op 2026-09-26. Plan: https://claude.ai/artifact/PfSVNgEGTfitCfCLoZ2UAF

| Label (NL) | Systeemnaam (EN) | Status |
|---|---|---|
| Ploeg | `team` | ✅ gebouwd, 6 velden |
| Leider | `leader` | ✅ gebouwd, 4 velden |
| Rondeboekje | `activity_booklet` | te bouwen |
| Nieuwsbericht | `article` (hergebruik) | ✅ label + `field_intro` + `field_main_image` |
| Basispagina | `page` | bestaat |
| Fotoalbum | `photo_album` | **buiten scope**, uitbreiding |

Velden van `team`: `field_photo`, `field_location`, `field_age_range`,
`field_email`, `field_weight`, `field_description`.
Velden van `leader`: `field_photo` (hergebruikt), `field_phone`,
`field_babysitting`, `field_team`.
Op `article`: `field_intro` en `field_main_image` (media). Het oude
`field_image` blijft bestaan maar staat verborgen in de formulierweergave.
Er is géén tags-veld op article; alleen de woordenlijst `tags` bestaat.

### Testcontent (26-09-2026)
9 ploegen, 20 leiders, 3 nieuwsberichten, 9 mediabeelden. Aangemaakt met
`drush php:script`, niet via de UI: bulk-invoer. De groepsfoto's komen uit
Figma (uitgesneden uit de pagina "leiding"), en staan in
`web/sites/default/files/ploegen/`. Telefoonnummers zijn nep (`0470/00 00 xx`).

Afgesproken keuzes:
- **Geen content type Evenement.** De agenda blijft in Google Agenda, ingesloten.
- **Geen login voor bezoekers.** Telefoonnummers van leiders staan publiek.
- **Alles wat een bestand is, gaat via Media.** Ook PDF's.
- Sorteren met `field_weight`, in stappen van 10. Niet op Leider.

## Regels voor dit project

- **Nederlands** in de interface, labels en veldnamen.
- Machine-namen in het Engels en `snake_case`, zoals Drupal gewoon is.
- Nooit de map `../drupal` aanraken. Dat is een andere site.
- Geen config-export zonder vragen. `cex` overschrijft stil.
- Media en foto's via de Media-module, niet als los bestandsveld.

## Bekende valkuilen

**1. Content types komen uit recipes, niet uit het profiel.**
Drupal 11.4 levert `standard` zonder Article en Basic page.
Die komen uit `web/core/recipes/page_content_type` en `article_content_type`.
Drush wil een absoluut pad in de container:
`ddev drush recipe /var/www/html/web/core/recipes/<naam>`.

**2. Recipes falen op een Nederlandse site.**
De install stempelt `langcode: nl` op core-config. Recipes verwachten `en`
en vergelijken strict. Zet `langcode` op `en` voor de config die de recipe
importeert, en draai hem dan opnieuw.

**3. Admin Toolbar doet niets.**
Core Navigation is actief en vervangt de toolbar. `admin_toolbar`,
`admin_toolbar_tools` en `gin_toolbar` staan aan maar renderen niets.
`drush pml` toont ze als "enabled". Dat is een valse groene regel.

**4. De Opslaan-knop in een Gin-dialoog zit ONDERAAN het venster.**
Bij "Veld toevoegen" staan er twee knoppen met hetzelfde label. Die ín het
formulier doet niets: het veld wordt stil niet opgeslagen, zonder foutmelding.
Klik altijd de knop in de dialoog-voet, buiten het `<form>`.
Controleer daarna met drush of het veld er echt staat:
`ddev drush ev 'print_r(array_keys(\Drupal::service("entity_field.manager")->getFieldDefinitions("node","<type>")));'`

## Wat we NIET doen

- Geen Experience Builder. Dat zit in het andere project.
- Geen headless of Next.js front-end. Gewoon Drupal-templates.
- Geen eigen module bouwen zolang een core- of contrib-oplossing bestaat.
- Niet pushen, niet deployen. Arne drukt zelf op de laatste knop.
