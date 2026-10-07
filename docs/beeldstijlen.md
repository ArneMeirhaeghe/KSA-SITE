# Beeldstijlen

Update 6 oktober 2026: Media-weergave **Artikelbeeld** gebruikt nu de bestaande
responsive stijl **Foto** (schalen zonder bijsnijden), zodat nieuwsaffiches
volledig zichtbaar zijn. CSS begrenst de hoogte en gebruikt `object-fit: contain`.
Deze instelling is via beheer opgeslagen; de eerdere inventaris hieronder
beschrijft de oorspronkelijke beeldstijlen. Zie `visuele-hierarchie.md`.

Alle beeldstijlen maken **AVIF**, met WebP als reserve.
Uploaden doe je altijd in de Media-bibliotheek. Drupal maakt de kleinere versies zelf.

## Overzicht

| Stijl | Effect | Gebruikt op |
|---|---|---|
| `hero_photo` Herofoto | bijsnijden 480 × 518 | 4 foto's in de hero (home) |
| `hero_background` / `_md` / `_sm` | schalen 2560 / 1440 / 800 breed | eigen achtergrond hero (responsive) |
| `article_wide` / `_md` / `_sm` | bijsnijden 2432×802 / 1216×401 / 800×264 | hoofdfoto nieuwsdetail (responsive) |
| `media_strip` Fotostrook | bijsnijden 686 × 802 | carrousel Ons verhaal |
| `info_card` Infokaart | bijsnijden 1054 × 660 | lokalen Ons verhaal |
| `contact_logo` Contactlogo | schalen 480 × 480 | taklogo's Contact |
| `wide` Wide (1090) | schalen 1090 breed | nieuws- en ploegkaarten, banner Ons verhaal |
| `large` Large (480) | schalen 480 | leiderskaarten |
| `medium`, `thumbnail`, `media_library` | klein | admin |

De module Responsive Image voegde ook `max_325x325` … `max_2600x2600` en de
responsive stijlen `narrow` en `wide` toe. Die gebruiken we niet.

## Responsive images

Core-module **Responsive Image** staat aan (via Uitbreiden).
Breakpointgroep: `responsive_image` (van de module zelf, één breakpoint met `sizes`).
De browser kiest dan zelf de breedte: een gsm laadt een kleiner bestand.

| Responsive stijl | Beelden | sizes |
|---|---|---|
| **Hero-achtergrond** | 800, 1440, 2560 | `(min-width: 700px) calc(100vw - 64px), calc(100vw - 32px)` |
| **Foto** | 800, 1440, 2560 (stijlen Hero-achtergrond) | `(min-width: 1280px) 1216px, (min-width: 700px) calc(100vw - 64px), calc(100vw - 32px)` |
| **Artikelbeeld** | 800, 1216, 2432 | `(min-width: 1280px) 1216px, (min-width: 700px) calc(100vw - 64px), calc((100vw - 32px) * 2)` |

Waarom `* 2` op gsm bij Artikelbeeld: de CSS snijdt de foto daar bij tot 3:2.
Van een foto van 3:1 heb je dan dubbel zoveel breedte nodig om scherp te blijven.

Koppeling via Media:

- Media-weergavemodi **Hero-achtergrond** en **Artikelbeeld** (type Image):
  Image → Responsive image, label verborgen, laden **eager**.
- Hero, veld Achtergrond: Rendered entity → Hero-achtergrond.
- Nieuwsbericht (Layout Builder), blok Hoofdafbeelding: Rendered entity → Artikelbeeld.
- Hero, veld Foto's: Media Thumbnail → Herofoto, laden eager.
- Bloktype Foto: Rendered entity → Media-weergave **Foto** → responsive stijl **Foto** (lazy). Zie `verhuur.md`.

## Controle (30-09-2026)

- Nieuwsdetail desktop: 2432-versie op een retina-scherm, 1216 × 401 getoond, hoek 12 px blijft.
- Nieuwsdetail 402 px: 3:2 blijft scherp.
- Hero-foto's, Fotostrook, Infokaart en Contactlogo laden als `.avif`.
- De hero-achtergrond kon niet getest worden: er is nog geen eigen achtergrond gekozen.
- De testfoto's zijn klein (±1000 px). Echte foto's worden daardoor pas echt lichter.

Ongebruikt bestand verplaatst: `backups/2026-09-30-beeldstijlen-ongebruikt/ksa.breakpoints.yml`
(eerst gemaakt, daarna bleek de breakpointgroep van de module genoeg).
