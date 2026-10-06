# Verhuur

De pagina staat op `/verhuur` (node 37, Basispagina).
Titel: **Welkom bij 't Brielhof!**. Het menu-item blijft **Verhuur**.
Ons verhaal linkt vanuit de kaart **Deinze** ("Lokaal huren") naar deze pagina.

## Zo beheer je het

| Wat | Waar |
|---|---|
| Titel en intro | Verhuur → Bewerken → Titel en **Intro** |
| Grote foto | Layout → blok **Foto 't Brielhof** (bloktype Foto) |
| Vier kenmerken | Layout → sectie **Kenmerken**, 4 × Infokaart (kop, tekst, foto) |
| Beschikbaarheid | Layout → blok **Beschikbaarheid & Boekingen** (Tekst met beeld, stijl **Compact**) |
| Knop Kampas | in hetzelfde blok, veld Knop: `https://www.kampas.be/nl/verblijf/t-brielhof-1` |
| Opmerking onder de knop | veld **Kleine tekst** |
| Route | Layout → sectie **Route**: Basic block (tekst) links, blok Foto (kaart) rechts |

### Een kenmerk aanpassen (bv. Groene omgeving)

1. Ga naar Verhuur en klik **Layout**.
2. Beweeg over de kaart en klik het **potloodje** rechtsboven → **Configure**.
3. Pas **Kop**, **Tekst** of **Foto** aan in het paneel rechts → **Bijwerken**.
4. Klik bovenaan **Save layout**. Pas dan is het live.

Het bovenste veld **Titel** in het paneel is alleen de naam in de layout; bezoekers zien die niet.
Een vijfde kenmerk? **Add section** met 4 kolommen, of een kaart in een lege kolom via **Add block** → Infokaart.

Boeken gebeurt volledig op Kampas. Er is geen formulier of boekingssysteem op de site.

## Drupal-instellingen

Alles via de admin-UI:

- Nieuw bloktype **Foto** (`photo`): `field_photo` hergebruikt (Media, Image, verplicht).
  Weergave: Rendered entity → Media-weergave **Foto** → responsive stijl **Foto**
  (800, 1440, 2560 breed, AVIF; zelfde beeldstijlen als Hero-achtergrond).
- Tekst met beeld (`text_media`): nieuwe velden `field_style` (**Stijl**: Groot / Compact, standaard Groot)
  en `field_note` (**Kleine tekst**, optioneel). Ons verhaal blijft Groot.
- Nieuwe media: 26 oever, 27 kaart, 28 speeltuin Brielmeersen, 29 zaal, 30 gebouw, 31 illustratie ksa-header.
- Layout van node 37:
  1. 1 kolom: Intro, lege body, Foto (oever).
  2. 4 kolommen (label Kenmerken): Groene omgeving, Energiezuinig, Goede bereikbaarheid, Capaciteit.
  3. 1 kolom (label Beschikbaarheid): Tekst met beeld, stijl Compact, achtergrond illustratie.
  4. 2 kolommen 50/50 (label Route): Basic block met h2 en vette tussenkopjes, Foto (kaart).

## Code

- `components/photo`: nieuwe foto-component. Het potloodje van Drupal (`.contextual`) blijft een eigen laag, zodat de foto niet verschuift bij hover.
- `components/photo` (vervolg): 1 kolom = 1216 × 401, in een kolom = 528 × 443.
- `text-media`: variant `compact` (omlijnde knop met pijltje, kleinere banner) en slot `note`.
- `info-card`: compacte weergave in een sectie met 4 kolommen (rand, foto 227 × 188, tekst 16).
- `css/layout.css`: raster voor 4 kolommen (gsm: 2), tekst naast foto (h2 36, tekst grijs, 12 px).
- Templates: `block--block-content--type--photo`, `block--block-content--type--text-media` (stijl + noot).

Back-up vooraf: `backups/2026-09-30-voor-verhuur/`. Figma-beelden: `prototypes/verhuur/figma-beelden/`.

## Ontwerp

Figma Desktop `50:709` en Mobile `128:1239` (één versie). Bouwplan 09.

| | Desktop | Mobiel |
|---|---|---|
| Foto | 1216 × 401, 44 px onder de intro | 366 breed, 20 px onder de intro |
| Kenmerken | 4 × 251, 81 px onder de foto | 2 kolommen, 16 px, 32 px tussen rijen |
| Banner | 1080, padding 32/24, 24 px tussen, titel 40 | titel 26, tekst 14 |
| Route | tekst 528 + kaart 528 × 443, 64 px onder de banner | kaart onder de tekst |
| Onder de route | 79 px tot de footer | idem |

Bewuste afwijkingen:

- Paginatitel 64 px (Figma 48), zoals Leiding en Contact.
- Een eigen bloktype **Foto** in plaats van de Fotostrook met één foto. De Fotostrook is nu een
  carrousel; één gewone foto met een andere uitsnede is eenvoudiger als apart blok.
- Kleine taalcorrecties: "recreatiedomein", "geïsoleerd", "Boekingen verlopen", "gps".
