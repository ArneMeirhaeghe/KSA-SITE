# Ploegenoverzicht (Leiding)

De pagina staat op `/leiding` (node 33, Basispagina).
Titel: **Maak kennis met de leiding**. Het menu-item blijft **Leiding**.

## Zo beheer je het

- **Ploeg aanpassen:** Inhoud → zoek de ploeg → Bewerken.
  Foto, plaats en leeftijd verschijnen vanzelf op de kaart.
- **Nieuwe ploeg:** Inhoud toevoegen → Ploeg. Publiceren is genoeg.
- **Volgorde:** veld **Volgorde** van de ploeg. Laag getal eerst.
  Bij een gelijk getal: alfabetisch op titel.
- **Intro onder de titel:** bewerk de pagina Leiding → veld **Intro**.

## Drupal-instellingen

Alles ingesteld via de admin-UI:

- Ploeg, weergave **Teaser**: Groepsfoto (Media Thumbnail, Wide (1090),
  zonder link, lazy), Plaats en Leeftijd. Labels verborgen.
  Beschrijving, E-mail, Volgorde en Links staan uit.
- View **Ploegen** (`teams`), blokweergave `block_1`.
  Gepubliceerde ploegen, sortering Volgorde oplopend en dan Titel.
  Alle ploegen, geen pager. Rijen: Content → Teaser.
- Veld `field_intro` hergebruikt op Basispagina (zelfde veld als bij Nieuws).
- Layout van node 33: Intro bovenaan (label verborgen), daaronder het blok
  Ploegen zonder bloktitel. Body blijft staan maar is leeg.
  De Links-placeholder is uit de layout gehaald.

## Componenten

- `card`: gedeelde kaartbasis voor nieuws (`variant: news`) en ploegen (`variant: team`).
- `card-grid`: gedeeld raster. Ploegen: twee kolommen op gsm.
- `team-overview`: breedte en marges rond het ploegenraster.
- `page-intro`: gecentreerde paginakop. Stijlt ook de paginatitel als er een intro is.
  Klaar voor Contact en Verhuur: vul daar het veld Intro in.
- `node--team--teaser.html.twig`, `views-view--teams.html.twig`,
  `field--node--field-intro--page.html.twig`: geven Drupal-velden door.

Views kiest de ploegen en de volgorde. Twig bepaalt alleen de weergave.
Geen preprocessors en geen nieuwe modules.

## Ontwerp

Figma Desktop `49:370` en Mobile `129:1505`, gelezen via MCP en Figma Web.

| | Desktop | Mobiel |
|---|---|---|
| Titel | 64 px, 83 px onder de navbar | 36 px, 39 px onder de navbar |
| Intro | 16 px, max 711 px, 7 px onder de titel | 14 px, 10 px onder de titel |
| Raster | 3 × 343 px, 25 px tussenruimte, 36 px onder de intro | 2 kolommen, 16 px, 39 px onder de intro |
| Kaart | foto 343 × 246, 16 px tot tekst, tekstblokken 12 px | foto 12 px tot tekst, tekstblokken 6 px |
| Titel kaart | 32 px | 20 px |
| Plaats · leeftijd | 16 px, op één regel met stip | 14 px, onder elkaar |

Bewuste afwijkingen, zoals bij Nieuws:

- Plaats en leeftijd in Dark Grey in plaats van Grey, voor leesbaar contrast.
- **Lees meer** in donkere tekst met een groene pijl, niet volledig groen.
  Groen op wit is te licht voor tekst.

## Controle (29-09-2026)

- 9 ploegen, in de volgorde van het veld Volgorde.
- Klik op de kaart (ook op de foto) opent de juiste ploeg.
- Desktop 1280 px: maten volgen Figma op 1 px na.
- 402 px en 320 px: twee kolommen, geen horizontale overloop in de site.
- Nieuwskaarten op `/home`: zelfde maten, kleuren en marges als voordien.

## Nog open

- Ploegen hebben nog geen nette URL (`/node/1`). Kan later met Pathauto
  of een alias per ploeg.
- De ploegpagina zelf: zie `docs/ploeg-detail.md`.
- Backups: `backups/2026-09-29-voor-leiding/` (ook de oude `news-card`).
