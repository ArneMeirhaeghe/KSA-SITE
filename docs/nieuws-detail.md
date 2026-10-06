# Nieuwsdetail

Elke Nieuwsbericht-pagina gebruikt dezelfde vaste opmaak (bouwplan 02).
Voorbeeld: `/node/30` (Startdag 2025).

## Zo beheer je het

1. Bewerk of maak een Nieuwsbericht.
2. **Intro**: twee tot drie zinnen onder de titel.
3. **Hoofdafbeelding**: de grote foto. Dezelfde foto verschijnt op de kaart.
4. **Body**: de tekst.
   - Een foto invoegen: knop **Drupal media** (Media Library). Kies daarna
     in het kleine menu op de foto **Align right and wrap text**.
   - Een knop maken: maak een link, selecteer die en kies **Stijlen → Knop**.
     Zet de knop op een eigen regel; hij staat dan gecentreerd.
5. De datum is de aanmaakdatum (**Authoring information**).

## Drupal-instellingen

Alles ingesteld via de admin-UI:

- Nieuwsbericht, standaardweergave: **Layout Builder aan**, aanpassen per
  artikel **uit**. Volgorde: Intro, Authored on, Hoofdafbeelding, Body,
  Meer nieuws. Labels verborgen. Oud veld Image en Links uit de layout gehaald.
- Hoofdafbeelding: Rendered entity, Media-weergave **Artikelbeeld** → responsive stijl
  **Artikelbeeld** (800, 1216, 2432, AVIF), laden **eager**. Zie `beeldstijlen.md`.
- Datum: Authored on, notatie `j F Y` (bv. 26 september 2026).
- View **Nieuws**, nieuw blok **Meer nieuws** (`block_2`): 3 berichten, geen pager,
  contextfilter Content ID uit de URL met **Exclude**. Blok zonder titel.
- Tekstformaat **Basic HTML**: knop Drupal media (filter Embed media aan),
  knop Image (upload) weg, stijl `a.button|Knop`.

## Componenten

- `article-detail`: kop, datum, grote foto, tekstkolom, afstand tot meer nieuws.
- `rich-text`: alinea's, foto links/rechts, stijl Knop.
- `page-intro` (variant `article`), `card` + `card-grid` via `news-overview`.
- Templates: `node--article--full`, blokken `block--field-block--node--article--*`.

## Ontwerp

Figma Desktop `18:61` (Blog Detail), gemeten via Figma Web.

| Blok | Maat |
|---|---|
| Titel | 64 px, 111 px onder de navbar, max 522 px |
| Intro | 16 px Dark Grey, 12 px onder de titel |
| Datum | 27 px onder de intro |
| Foto | 1216 × 401, hoek 12, 85 px onder de datum |
| Tekst | kolom 528 px, 65 px onder de foto, 36 px tussen alinea's |
| Foto in tekst | 246 × 192, rechts, 36 px naast de tekst |
| Knop | 48 px hoog, hoek 8, 49 px onder de tekst |
| Meer nieuws | 3 kaarten, 59 px onder de knop |

Bewuste keuzes:

- **Geen auteur**: alleen de datum (bouwplan: auteur enkel als bewust gewenst).
- **Tekst links uitgelijnd** in plaats van uitgevuld: beter leesbaar.
- Grote foto: sinds 30-09-2026 een responsive image style (core-module Responsive Image staat aan).

## Controle (29-09-2026)

- Desktop: alle afstanden volgen Figma op 1 à 2 px na.
- 402 en 320 px: geen horizontale overloop; foto in de tekst staat dan onder elkaar.
- Meer nieuws toont het huidige artikel niet.
- Nieuwskaarten op /home: zelfde maten als voordien.
- De mobiele Figma-frames zijn nog niet nagemeten.
