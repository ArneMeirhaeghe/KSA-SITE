# Beeldstijlen

Bijgewerkt op 17 november 2026, na de audit vóór de livegang.

Uploaden doe je altijd in de Media-bibliotheek. Drupal maakt de kleinere versies zelf.
Alle stijlen maken **AVIF**, met WebP als reserve. Eén uitzondering: de deelfoto is **JPEG**.

De naam van een stijl zegt waar hij voor dient. Zo kies je in de UI meteen de juiste.

## Welke stijl waar

Beheer: **Instellingen → Media → Afbeeldingsstijlen**.

| Stijl in de UI | Systeemnaam | Effect | Gebruikt op |
|---|---|---|---|
| Kaart: nieuws (760×546, bijgesneden) | `card` | schalen en bijsnijden | nieuwskaarten (home, Nieuws) |
| Kaart: ploeg (past in 760×546) | `card_fit` | schalen, niets afsnijden | ploegkaarten (Leiding) |
| Leiderkaart en standaardweergave (480×480) | `large` | schalen | leiderkaarten, standaard Media-weergave |
| Tekst met beeld (1090 breed) | `wide` | schalen | bloktype Tekst met beeld: stappen, tabbladen, banners |
| Herofoto (480×518) | `hero_photo` | bijsnijden | de foto's aan de waslijn (home) |
| Fotostrook (686×802) | `media_strip` | bijsnijden | carrousel op Ons verhaal |
| Infokaart (1054×660) | `info_card` | bijsnijden | kaarten met foto (lokalen, verhuur) |
| Contactlogo (480) | `contact_logo` | schalen | taklogo's (Contact, rondeboekjes) |
| Volle breedte (800 / 1440 / 2560) | `hero_background_sm` / `_md` / `hero_background` | schalen | bouwstenen van de responsive stijlen hieronder |
| Deelfoto (1200, JPEG) | `social_share` | schalen binnen 1200×900, als JPEG | het beeld bij een gedeelde link (WhatsApp, Facebook) |
| Gemiddeld (220×220), Miniatuur (100×100), Mediabibliotheek miniatuur (220×220) | `medium`, `thumbnail`, `media_library` | klein | alleen in het beheer |

### Waarom twee kaartstijlen

- Een nieuwskaart **snijdt** de affiche bij tot het kaartformaat. De stijl doet dat al, dus er gaat geen overbodig beeld over de lijn.
- Een ploegkaart toont de **hele** groepsfoto. Daarom schaalt die stijl alleen, zonder te snijden.

Vóór 16 november gebruikten beide de stijl van 1090 breed.
De pagina Leiding laadde daardoor 1295 KB aan kaartfoto's. Nu 403 KB.

### Waarom de deelfoto JPEG is

WhatsApp en Facebook lezen AVIF niet betrouwbaar. De deelfoto is ook bewust klein:
de zwaarste is nu 265 KB. Het origineel woog tot 3,3 MB.

De koppeling staat in **Instellingen → Zoeken en metadata → Metatag**, bij Nieuwsbericht, Ploeg en Leider:
`[node:field_main_image:entity:field_media_image:social_share:url]` (bij Ploeg en Leider `field_photo`).

## Responsive stijlen

Beheer: **Instellingen → Media → Responsive afbeeldingsstijlen**.
De browser kiest zelf de breedte: een gsm laadt een kleiner bestand.

| Responsive stijl | Kiest uit | Gebruikt op |
|---|---|---|
| **Foto** | Volle breedte 800, 1440, 2560 | hoofdfoto van een nieuwsbericht, bloktype Foto |
| **Hero-achtergrond** | Volle breedte 800, 1440, 2560 | eigen achtergrond van de hero |

Koppeling via Media-weergavemodi (type Image):

- **Artikelbeeld** en **Foto** → responsive stijl Foto.
- **Hero-achtergrond** → responsive stijl Hero-achtergrond.

Een veld gebruikt die weergavemodus met de opmaak **Weergegeven entiteit**.

## Een stijl aanpassen of toevoegen

1. Zoek in de tabel hierboven welke stijl het onderdeel gebruikt.
2. Pas de maat aan in de stijl zelf. Elke plek die de stijl gebruikt, volgt mee.
3. Nieuwe stijl nodig? Zet eerst **Schalen** (of **Schalen en bijsnijden**), daarna **Omzetten naar AVIF** met reserve WebP.
4. Koppel hem bij **Structuur → Inhoudstypen (of Bloktypes) → Weergave beheren**, tandwiel naast het veld.

Geen stijlnamen in Twig of PHP: de keuze staat altijd in de weergave van het veld.

## Opgeruimd op 16 november 2026

Verwijderd omdat niets ze gebruikte:

- Afbeeldingsstijlen `article_wide`, `article_wide_md`, `article_wide_sm` (vervangen door de responsive stijl Foto op 6 oktober).
- Afbeeldingsstijlen `max_325x325`, `max_650x650`, `max_1300x1300`, `max_2600x2600` en de responsive stijlen `narrow` en `wide`: standaard van Drupal, hier nooit gebruikt.
- Responsive stijl `article_wide`.

De bestanden staan nog in `config/sync/`. Bij een volgende export verdwijnen ze daar ook.

## Nog te doen

- De foto's van de leiders zijn maar 172 px breed. Op een gsm-scherm oogt dat onscherp. Dat lost alleen een grotere upload op.
- Kaartfoto's zijn niet responsive: de opmaak Miniatuur kan dat niet. Op een gewoon desktopscherm laadt de browser daardoor nog wat te veel.
- De server van productie moet AVIF kunnen maken (PHP met GD en AVIF). Lukt dat niet, dan valt Drupal terug op WebP.
