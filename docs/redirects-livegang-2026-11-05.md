# Redirects voor de livegang — 5 november 2026

Oude site: `ksadeinze.be` (Fork CMS). Geen sitemap, dus volledig gecrawld:
**213 adressen**. Mapping: [redirects-livegang-2026-11-05.csv](redirects-livegang-2026-11-05.csv).

**Uitgevoerd op 16 november 2026**, lokaal. Zie onderaan: "Uitgevoerd".
Op de testsite en op productie staan ze nog niet.

Kolom `action` in de CSV:

- `create`: redirect aanmaken (50 rijen).
- `same_path`: zelfde adres op beide sites, **geen** redirect nodig (5 rijen).
  Staan erin als bewijs dat ze bekeken zijn.

## Wat er al was

24 redirects sinds 7 oktober (zie `seo-uitvoering-2026-10-07.md`).
Lokaal én op de Alwaysdata-testsite aanwezig. Die blijven zoals ze zijn.

## Nieuw: 50 redirects

| Groep | Aantal | Doel | Waarom |
|---|---|---|---|
| Nieuws met een tegenhanger | 5 | het nieuwe bericht | Titel én datum komen overeen. `quiz-4` (10-12-2023) = Quiz 2024. |
| Oud nieuws zonder tegenhanger | 24 | `/nieuws` | Niet overgezet. Het archief is de beste landing. |
| Nieuwsoverzicht `?page=1-4` | 4 | `/nieuws?page=0` | Zonder deze regel wordt `/blog?page=3` → `/nieuws?page=3` → **404**. Het doel is `?page=0`: zie "Uitgevoerd". |
| RSS | 1 | `/rss.xml` | Feedlezers blijven werken. |
| Ploegpagina's `leiding/detail/1-7` | 7 | `/ploegen/...` | Koppen op de oude site gecontroleerd. |
| Boekjes 2 (pdf) | 7 | `/rondes` | Staan niet op de nieuwe site. Zie open vragen. |
| `stappenplan-ravot.pdf`, `ksa-rasp.pdf` | 2 | `/inschrijving` | Staan niet op de nieuwe site. |

## Bewust niet

- **Zelfde adres op beide sites:** `/`, `/leiding`, `/contact`, `/rondes`, `/inschrijving`.
  Een redirect zou de echte pagina breken.
- **Afbeeldingen** (`Blog/images`, `Users/images`, `leaders/*.jpg`).
  Een beeld naar een HTML-pagina sturen is slechte praktijk.
- `reacties-op-rss/*` en `reacties-rss`: reacties bestaan niet meer.
- Vier starttocht-pdf's: geven nu al 404.
- `jquery.min.js`.

## Open vragen

1. **Kerstrozen 2026** is een lopende actie en staat níet op de nieuwe site.
2. **Boekjes 2** zijn de actuele boekjes. De nieuwe `/rondes` toont nog boekje 1.
   Dat is een inhoudsprobleem vóór livegang.
3. `stappenplan-ravot.pdf` en `ksa-rasp.pdf` ontbreken op de nieuwe site.
4. Welke omgeving wordt productie? Redirects zijn database-inhoud, geen config.
   Ze moeten in díe database staan.

## Bij de domeinwissel (server, niet Drupal)

`ksadeinze.be` in `trusted_host_patterns`, `www` → zonder `www`, en `http` → `https`.

## Controle na aanmaken

Per CSV-rij: één 301, en het doel geeft meteen 200. Geen ketens.

## Uitgevoerd op 16 november 2026

Lokaal aangemaakt via **Instellingen → Zoeken en metadata → URL-omleidingen**: 51 stuks.
Er staan nu 75 redirects in de lokale database.

- De 50 rijen uit de CSV.
- Eén extra: `/ploegen` → `/leiding`. Dat adres gaf 404, terwijl `/ploegen/knim` wel bestaat.

Eén afwijking van het eerste voorstel:

- `blog?page=1` tot `4` gaan naar **`/nieuws?page=0`**, niet naar `/nieuws`.
- Reden: de module Redirect geeft de vraagtekst van de bezoeker door (instelling
  "Querystring doorgeven"). Met doel `/nieuws` werd `/blog?page=3` dus toch `/nieuws?page=3`, en dat is een 404.
- De eerdere vermelding "Getest" bij deze rij klopte niet. De CSV is aangepast.
- `/nieuws?page=0` toont de eerste pagina. De canonieke link wijst naar `/nieuws`.

Controle: elke rij geeft één 301 en het doel geeft meteen 200 (51 op 51).
`scripts/check-seo.php` bewaakt dit voor alle 75 redirects.

Let op bij het overzetten:

- Redirects zijn inhoud, geen configuratie. Ze moeten in de database van productie staan.
- Draai daarna `drush cr`. Een oude doorverwijzing kan anders in de paginacache blijven hangen.
