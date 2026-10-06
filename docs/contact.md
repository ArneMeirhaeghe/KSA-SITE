# Contact

De pagina staat op `/contact` (node 38, Basispagina).
Titel: **Vragen? Contacteer ons!**. Het menu-item blijft **Contact**.

## Zo beheer je het

| Wat | Waar |
|---|---|
| E-mailadres van een ploeg | Inhoud → ploeg → Bewerken → **E-mailadres**. Leeg = geen kaart. |
| Kop van een ploegkaart | Ploeg → **Contacttitel**. Leeg = naam van de ploeg. Hoofdleiding: "Algemene vragen & contact met de hoofdleiding". |
| Logo achter de tekst | Ploeg → **Logo** (Media). Leeg = kaart zonder logo. Niet de groepsfoto gebruiken. |
| Volgorde | Ploeg → **Volgorde**. Laag getal eerst, daarna alfabetisch. Zelfde volgorde als Leiding. |
| Verhuurkaart | Contact → Layout → blok **Verhuurcontact** (bloktype Contact). Kop, e-mail, telefoon (optioneel), logo. |
| Intro onder de titel | Contact → Bewerken → **Intro**. |

Het e-mailadres staat alleen bij de ploeg. Er zijn geen aparte contactnodes of kopieën.
Een nieuwe gepubliceerde ploeg met e-mailadres verschijnt vanzelf op Contact.

## Drupal-instellingen

Alles ingesteld via de admin-UI:

- Ploeg: nieuwe velden `field_logo` (Media, Image, optioneel) en `field_contact_title` (tekst, optioneel).
- Weergavemodus **Contact** (`node.contact`), alleen ingeschakeld voor Ploeg:
  Logo (Media Thumbnail, beeldstijl `contact_logo`), E-mailadres (mailto), Contacttitel. Labels verborgen.
- Beeldstijl **Contactlogo (480)** (`contact_logo`): schalen tot 480 × 480.
- View **Contactkaarten** (`contact_cards`), blok `block_1`:
  gepubliceerde ploegen met e-mailadres, sortering Volgorde en dan Titel, alle items, rijen = Content → Contact.
- Bloktype **Contact** (`contact`): `field_heading` (hergebruikt, verplicht), `field_email` (verplicht),
  `field_phone` (optioneel), `field_logo` (optioneel). Weergave: mailto, telefoonlink, logo `contact_logo`.
- Layout van node 38:
  1. Sectie 1 (1 kolom): Intro, lege body, blok Contactkaarten (zonder titel). Links-placeholder verwijderd.
  2. Sectie 2 (1 kolom, label Verhuurcontact): inline blok Contact met `verhuur@ksadeinze.be` en het verhuurlogo.
- Layout-template van Ploeg (standaard): het automatisch toegevoegde Logo-veld weer weggehaald.

## Code

- `contact-card`: variant `tile` (rand, radius 12, logo op 20 %), nieuwe slots `heading`, `extra`, `logo`.
  De oude weergave op de ploegpagina blijft hetzelfde.
- `contact-overview`: raster (card-grid) en marges rond de ploegkaarten.
- `node--team--contact.html.twig`, `views-view--contact-cards.html.twig`,
  `block--block-content--type--contact.html.twig`: geven Drupal-velden door.
- `css/layout.css`: een Contact-blok in een 1-kolomsectie wordt een extra tegel in hetzelfde raster.

Back-up vooraf: `backups/2026-09-30-voor-contact/`.
Logo's uit Figma: `prototypes/contact/figma-logos/` (media 17–25).

## Ontwerp

Figma Desktop `107:2602` (bouwplan 08, gekleurde logo's). Er is geen mobiel Contact-frame.

| | Desktop | Mobiel (eigen keuze) |
|---|---|---|
| Kaart | 343 × 234, rand #d9d9d9, radius 12, padding 30/24 | 1 kolom, min. 180 hoog, padding 24/20 |
| Kop kaart | 24/24 semibold | 20 px |
| E-mail | 16/24, onderaan | 14 px |
| Raster | 3 kolommen, 25 px tussenruimte, 40 px onder de intro | 16 px tussenruimte |
| Onder de kaarten | 135 px tot de footer (marges vallen samen met die van de footer) | 50 px |
| Logo | 20 % dekking, volle kaartbreedte, midden | idem |

Bewuste afwijkingen:

- Paginatitel 64 px zoals Leiding (Figma Contact: 48 px). Eén gedeelde stijl voor alle basispagina's met intro.
- E-mail donker met groene onderlijn, zoals op de ploegpagina. Zo zie je dat het een link is.
- Logo's vullen de kaartbreedte. In Figma is dat per kaart verschillend (FILL of FIT).
- Tablet (2 kolommen): de verhuurkaart start op een nieuwe rij, omdat ze in een eigen sectie staat.
