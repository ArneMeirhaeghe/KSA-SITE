# Nieuwsoverzicht

Het overzicht staat onder de hero op `/home` (node 39).

## Zo beheer je het

1. Ga naar **Inhoud → Inhoud toevoegen → Nieuwsbericht**.
2. Vul titel, intro, hoofdafbeelding en artikeltekst in.
3. Publiceer het bericht.

Het overzicht werkt vanzelf bij. Je hoeft de homepage niet te bewerken.
De bestaande drie berichten bevatten nog testteksten en groepsfoto’s.

## Drupal-instellingen

Alles ingesteld via de admin-UI:

- View **Nieuws** (`news`), blokweergave `block_1`.
- Alleen gepubliceerde Nieuwsberichten.
- Datum **Authored on** aflopend: nieuwste datum eerst.
- Bij een gelijke datum: hoogste artikel-ID eerst, voor een vaste volgorde.
- Zes berichten per pagina, met de volledige Drupal-paginering.
- Geen resultaten: het blok wordt verborgen.
- Weergave van elke rij: **Content → Teaser**.
- In Layout Builder onder de hero geplaatst, zonder zichtbare bloktitel.

De kaartdatum gebruikt de bestaande aanmaakdatum, dezelfde als de sortering.
Dit is geen apart veld voor een publicatiedatum. Een oud concept krijgt
niet automatisch een nieuwe datum wanneer je het later publiceert.
Je kunt de datum aanpassen bij **Authoring information** van het bericht.

## Bestaande velden hergebruikt

De teaser toont `field_intro` en `field_main_image`, zonder veldlabels.
De hoofdafbeelding gebruikt de core Media Thumbnail-formatter, image style
**Wide (1090)**, zonder link, met lazy loading.
Het oude afbeeldingsveld, Body en de standaard Links staan uit in de teaser.
De velden en hun inhoud zijn niet verwijderd. De detailweergave is niet aangepast.

## Componenten

- `card` (variant `news`): afbeelding, datum, titel, intro en Lees meer.
  Gedeeld met de ploegkaarten; zie `docs/leiding.md`.
- `news-overview`: breedte, marges en paginering. Het raster is `card-grid`.
- `node--article--teaser.html.twig`: geeft bestaande Drupal-velden door.
- `views-view--news.html.twig`: geeft View-resultaten door aan het raster.

Views selecteert inhoud. Twig bepaalt alleen de weergave.
Geen preprocessors, entity-query’s of nieuwe modules.
De artikel-URL komt van Drupal en volgt dus automatisch een eventuele alias.

De kaart heeft één toegankelijke link op de titel. Het klikvlak omvat de
kaart; Lees meer is de zichtbare uitnodiging, zonder dubbele tabstop.

## Ontwerp

Figma Desktop `44:265`: 1079 px breed, drie kolommen, 25 px tussenruimte.
Kaartbeeld: 343 × 246 px, afgeronde hoeken van 12 px.
Titel: h4-stijl, Inter 600, 32 px. Afbeelding tot tekst: 12 px.
Figma Mobile `121:1947`: één kolom, 36 px tussenruimte.
Op tablet gebruiken we twee kolommen.

De bestaande 1080 px-container en ontwerptokens worden hergebruikt.
Datum en intro gebruiken de donkerdere bestaande tekstkleur voor leesbaarheid.
Lees meer gebruikt donkere tekst met een groene pijl.
De intro toont maximaal twee regels; de volledige tekst blijft op het artikel.

## Controle

- Opgeslagen View en teaserconfiguratie nagelezen.
- Drie bestaande berichten verschijnen, met geladen Media-afbeeldingen.
- Nieuwskaart opent het juiste artikel.
- Desktop 1280 px: drie kolommen.
- Mobiel 402 px en 320 px: één kolom, geen horizontale overloop.
- Met drie bestaande berichten is er nu één pagina; de pager verschijnt
  automatisch zodra er meer dan zes gepubliceerde berichten zijn.

De detailpagina van een bericht: zie `docs/nieuws-detail.md`.
