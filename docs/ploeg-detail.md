# Ploegdetail

Elke Ploeg-pagina gebruikt dezelfde vaste opmaak (bouwplan 05).
Voorbeeld: `/node/2` (Dolfijntjes).

## Zo beheer je het

- **Leider toevoegen**: Inhoud toevoegen → Leider. Vink de **Ploeg** aan.
  De leider verschijnt vanzelf op die ploegpagina, alfabetisch.
- **Twee ploegen** (bv. Hoofdleiding en Sjo): vink beide aan. Sinds 30-09-2026 is Ploeg een veld met
  meerdere waarden (vinkjes). De koppeling blijft bij de leider: één formulier, niets te vergeten.
- **Telefoon** leeg laten mag: dan komt er geen lege link.
- **Mag gebeld worden voor babysitten** aanvinken: babyicoon met uitleg.
- **E-mailadres** van de ploeg: in het ploegformulier. Geen e-mail = geen contactblok.

## Drupal-instellingen

Alles ingesteld via de admin-UI:

- Ploeg, standaardweergave: **Layout Builder aan**, aanpassen per ploeg **uit**.
  Blokken: Leiders, E-mailadres (formatter E-mail = mailto, label verborgen).
  Groepsfoto, Plaats, Beschrijving, Leeftijd, Volgorde en Links uit de layout
  gehaald (de velden en hun inhoud bestaan nog).
- Leider: nieuwe weergave **Leiderskaart** (`leader_card`): Foto (Thumbnail,
  Large 480, lazy), Telefoon (Telephone link), Babysitten. Labels verborgen.
- View **Leiders** (`leaders`), blok: gepubliceerde leiders, alfabetisch, alle items.
  Contextfilter **Ploeg**, standaard Content ID uit de URL, validatie Ploeg.
  In Layout Builder gekoppeld aan de ploeg van de pagina. Leeg = blok verborgen.

## Componenten

- `team-detail`: titel links, raster, lijn en contact.
- `card` (variant `leader`, zonder link) + `card-grid` (4 kolommen).
- `contact-card`: kop, korte uitleg en e-mail. Klaar voor Contact.
- Templates: `node--team--full`, `node--leader--leader-card`,
  `views-view--leaders`, `block--field-block--node--team--field-email`.

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

## Controle (29-09-2026)

- Desktop: alle afstanden volgen Figma.
- 402 en 320 px: twee kolommen, geen horizontale overloop.
- Tooltip werkt bij hover.
- De mobiele Figma-frames zijn nog niet nagemeten.

## Nog open

- Nette URL's (bv. `/leiding/dolfijntjes`): Pathauto staat al klaar, er is
  alleen nog geen patroon.
- Het potloodmenu van Layout Builder opent niet in het KSA-thema.
