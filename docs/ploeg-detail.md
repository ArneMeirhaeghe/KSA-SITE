# Ploegdetail

Elke Ploeg-pagina gebruikt dezelfde vaste opmaak (bouwplan 05).
Voorbeeld: `/ploegen/dolfijntjes` (`/node/2`).

## Zo beheer je het

- **Leider toevoegen**: Inhoud toevoegen → Leider. Vink de **Ploeg** aan.
  De leider verschijnt vanzelf op die ploegpagina, alfabetisch.
- **Twee ploegen** (bv. Hoofdleiding en Sjo): vink beide aan. Sinds 30-09-2026 is Ploeg een veld met
  meerdere waarden (vinkjes). De koppeling blijft bij de leider: één formulier, niets te vergeten.
- **Telefoon** leeg laten mag: dan komt er geen lege link.
- **Mag gebeld worden voor babysitten** aanvinken: babyicoon met uitleg.
- **E-mailadres** van de ploeg: in het ploegformulier. Geen e-mail = geen contactblok.
- **Plaats, Leeftijd en Beschrijving**: in het ploegformulier. Ze staan bovenaan de ploegpagina.
  De leeftijd staat ook in de titel voor Google, bv. "Knim: 1ste en 2de middelbaar".

## Drupal-instellingen

Alles ingesteld via de admin-UI:

- Ploeg, standaardweergave: **Layout Builder aan**, aanpassen per ploeg **uit**.
  Blokken, in deze volgorde: Plaats, Leeftijd, Beschrijving, Leiders, E-mailadres
  (formatter E-mail = mailto). Alle labels verborgen.
  Groepsfoto, Volgorde en Links zitten niet in de layout (de velden en hun inhoud bestaan nog).
- Plaats, Leeftijd en Beschrijving waren eerst uit de layout gehaald, om Figma te volgen.
  Op 16 november 2026 zijn ze teruggezet, na de audit. Zie "Afwijking van Figma" hieronder.
- Leider: nieuwe weergave **Leiderskaart** (`leader_card`): Foto (Miniatuur,
  stijl Leiderkaart 480, lazy), Telefoon (Telephone link), Babysitten. Labels verborgen.
- View **Leiders** (`leaders`), blok "Leiderkaarten van één ploeg (ploegpagina)":
  gepubliceerde leiders, alfabetisch, alle items.
  Contextfilter **Ploeg**, standaard Content ID uit de URL, validatie Ploeg.
  In Layout Builder gekoppeld aan de ploeg van de pagina. Leeg = blok verborgen.

## Componenten

- `team-detail`: titel links, plaats en leeftijd, beschrijving, raster, lijn en contact.
- `card` (variant `leader`, zonder link) + `card-grid` (4 kolommen).
- `contact-card`: kop, korte uitleg en e-mail. Klaar voor Contact.
- Templates: `node--team--full`, `node--leader--leader-card`,
  `views-view--leaders`, `block--field-block--node--team--field-email`,
  en sinds 16 november `…--field-location`, `…--field-age-range` en `…--field-description`.

## Ontwerp

Figma Desktop `91:610` (Leiding Detal), gemeten via Figma Web.

| Blok | Maat |
|---|---|
| Titel | 64 px, links, 84 px onder de navbar |
| Raster | 4 × 251 px, 25 px tussenruimte, 26 px onder de titel |
| Leiderkaart | foto 251 × 330, hoek 12; naam 24/24; 16 en 12 px ertussen |
| Lijn | 2 px Light Grey, 53 px onder de leiders |
| Contact | 61 px onder de lijn; kop 32 px, tekst en e-mail 16 px |

Bewuste keuzes:

- Telefoon en e-mail in **donkere tekst met groene lijn**: groen op wit is te licht.
- Babysitten: tooltip bij hover **en** bij toetsenbordfocus; de tekst is ook
  voor schermlezers.
- Telefoon- en babyicoon zijn eenvoudige eigen SVG's. De Figma-iconen konden
  niet gedownload worden (MCP-limiet). Vervang ze gerust door de echte.
- Geen aparte leiderpagina (bouwplan): de kaart linkt nergens naartoe.

## Afwijking van Figma (16-11-2026)

Goedgekeurd door Arne op 23 november 2026.

Figma `91:610` toont alleen titel, leiders en contact. Een ploegpagina had daardoor 28 tot 47 woorden.
Een ouder die via Google op "Knim" landt, zag niet voor welke leeftijd of plaats de groep is.

Nu staat boven de leiders:

- één regel **Plaats · Leeftijd**, in dezelfde stijl als op de ploegkaart (`card.css`);
- de **Beschrijving**, maximaal 711 px breed, zoals de paginakop.

Op een gsm staan plaats en leeftijd onder elkaar, zonder punt. Dat doet de ploegkaart ook.
Er zijn geen nieuwe tokens. Terugdraaien kan in Layout Builder: verwijder de drie blokken.

## Controle (29-09-2026)

- Desktop: alle afstanden volgen Figma.
- 402 en 320 px: twee kolommen, geen horizontale overloop.
- Tooltip werkt bij hover.
- De mobiele Figma-frames zijn nog niet nagemeten.

## Controle (17-11-2026)

- Desktop: plaats en leeftijd op één regel, beschrijving eronder, dan de leiders.
- 375 en 320 px: gestapeld, 14 px, geen horizontale overloop.
- Niets verspringt bij het laden (opmaak vóór en na JavaScript gelijk).
- Het potloodmenu van Layout Builder opent in het KSA-thema (Instellen, Verplaatsen, Blok verwijderen).
- `scripts/check-seo.php` controleert dat plaats, leeftijd en beschrijving op `/ploegen/knim` staan.

## Nog open

- De link "← Alle leiding" en het actieve menu-item kennen de pagina Leiding aan haar nummer (`/node/33`).
  Zie `navigatie.md`.
