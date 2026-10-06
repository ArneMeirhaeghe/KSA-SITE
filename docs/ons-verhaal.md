# Ons verhaal

`/ons-verhaal` (`/node/36`). Figma: bouwplan 03, Desktop `29:2`, Mobile `125:1157`.
Er is één desktop- en één mobiele versie. Gebouwd op 30 september 2026.

## Opbouw (Layout Builder, eigen lay-out voor deze pagina)

| # | Sectie | Blok | Component |
|---|---|---|---|
| 1 | Eén kolom "Fotostrook" | **Fotostrook** (carrousel, 5 foto's) | `media-strip` + grijze lijn |
| 2 | Drie kolommen 25/50/25 "Verhaal" | Basic block, middelste kolom | `rich-text` |
| 3 | Eén kolom "Banner Onze leiding" | **Tekst met beeld** | `text-media` |
| 4 | Eén kolom "Onze lokalen: intro" | Basic block met h2 + tekst | `rich-text` (sectie-intro) |
| 5 | Twee kolommen 50/50 "Onze lokalen: kaarten" | 2× **Infokaart** | `info-card` |

- Staat er een Fotostrook bovenaan, dan is de paginatitel alleen voor schermlezers en valt het kruimelpad weg.

## Bloktypes (Structuur → Bloktypes)

| Label | Machine | Velden |
|---|---|---|
| Fotostrook | `media_strip` | `field_photos` Foto's (media: afbeelding, onbeperkt, verplicht) |
| Tekst met beeld | `text_media` | `field_heading` Titel, `field_intro` Tekst, `field_background` Afbeelding, `field_link` Knop |
| Infokaart | `info_card` | `field_heading` Titel, `field_intro` Tekst, `field_photo` Foto, `field_link` Knop (optioneel) |

- Hergebruikt van Hero: `field_heading`, `field_intro`, `field_background`. Nieuw: `field_photos`, `field_link`, `field_photo`.
- Nieuwe beeldstijlen: **Fotostrook (686×802)** `media_strip`, **Infokaart (1054×660)** `info_card`. Banner gebruikt **Wide (1090)**.

## Zo beheer je het

- **Foto's wisselen:** Lay-out → potlood op het blok → Configureren → Media toevoegen. Volgorde = volgorde op de pagina.
- **Fotostrook:** kies minstens 3 foto's, zoveel als je wil. Ze verspringen vanzelf: boven, onder, boven, onder (100 px)
  en schuiven eindeloos voorbij (40 px per seconde).
- **Tekst met beeld:** laat Knop leeg voor een banner zonder knop.
- **Nog een lokaal of infoblok:** een extra sectie met 2 kolommen, een Infokaart per kolom.

## Thema

- Componenten: `components/media-strip`, `components/text-media`, `components/info-card`.
- Templates: `templates/block/block--block-content--type--{media-strip,text-media,info-card,basic}.html.twig`.
- `css/layout.css` (nieuw, in de basisbibliotheek): secties met 2 of 3 kolommen in de 1080 px-kolom, 25 px tussenruimte.
  25/50/25 zonder tussenruimte = gecentreerde tekstkolom van 540 px.
- Basic block in een sectie met 1 kolom = sectie-intro (h2 48/64, tekst grijs, 711 px). In een kolom = lopende tekst.

## Carrousel (media-strip.js)

- JavaScript kopieert de foto's tot de strook breder is dan het scherm en schuift telkens één volledige reeks op: geen naad.
- Boven/onder wordt per positie gezet (`data-offset`). Bij een oneven aantal is één ronde twee reeksen lang,
  zodat boven/onder ook over de naad blijft afwisselen.
- Geen pauzeknop (keuze van Arne). Toetsenbordfocus in de strook pauzeert; hoveren niet.
- Backup met pauzeknop: `backups/2026-09-30-voor-geen-pauzeknop/`.
- Minder bewegen (`prefers-reduced-motion`) of geen JavaScript: stilstaande, gecentreerde strook.
- Snelheid: `SPEED` bovenaan `components/media-strip/media-strip.js`.
- Backup van voor de carrousel: `backups/2026-09-30-voor-carrousel/`.

## Keuzes

- Verhaaltekst links uitgelijnd (Figma: uitgevuld), 16/24 met een lege regel tussen alinea's. Daardoor iets hoger dan in Figma.
- Foto's: uit Figma gehaald (het zijn beelden van de oude site, niet uit `../fotos`). Bronbestanden: `prototypes/ons-verhaal/figma-beelden/`.
- De footer bestaat nog niet op de site; tot dan 50 px ruimte onder de laatste sectie.

## Gecontroleerd

- Anoniem, 1280 px: posities binnen 1–15 px van Figma (verschil komt van de hogere verhaaltekst).
- Mobiel 402 px: fotostrook, lijn, banner, intro en kaarten op 1–3 px; geen horizontale overloop.
- Layout Builder: de blokken tonen hun opmaak en de links Configureren/Verwijderen werken.

Backup van voor deze pagina: `backups/2026-09-30-voor-ons-verhaal/`.

## Update 30-09-2026

- Infokaart **Deinze** heeft nu een link **Lokaal huren** naar Verhuur (`entity:node/37`, blijft werken als de URL wijzigt).
- Tekst met beeld heeft een veld **Stijl**. De banner Onze leiding gebruikt Groot (standaard).
