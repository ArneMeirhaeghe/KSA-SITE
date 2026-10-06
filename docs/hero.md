# Hero: inhoud

Via de admin-UI aangemaakt:

| Veld | Type | Aantal | Verplicht |
|---|---|---|---|
| Titel (`field_heading`) | Korte platte tekst | 1 | Ja |
| Intro (`field_intro`) | Lange platte tekst | 1 | Nee |
| Foto’s (`field_images`) | Mediareferentie, alleen Image | maximaal 4 | Ja |
| Achtergrond (`field_background`) | Mediareferentie, alleen Image | 1 | Nee |

De foto’s worden gekozen via de Media-bibliotheek. De volgorde kan worden
aangepast. Verplicht betekent minimaal één foto; niet verplicht vier.
Veldlabels worden niet aan bezoekers getoond.

## Hergebruik

Vooraf gecontroleerd via **Re-use an existing field**. Er was alleen het
Body-veld van Basic block beschikbaar. Dit is opgemaakte tekst en past niet
bij de eenvoudige titel/intro van de hero.

De nieuwe velden hebben algemene namen, zodat andere bloktypes ze later
kunnen hergebruiken. Dat doen we alleen als betekenis, type en opslag passen.
Het maximumaantal waarden is gedeeld. Een blok met één foto of een galerij
met onbeperkte foto’s heeft dus een ander veld nodig.

Velden van inhoudstypes zoals Nieuwsbericht horen bij `node`.
Hero hoort bij `block_content`. Drupal deelt veldopslag alleen binnen
hetzelfde entiteitstype. Een gelijke veldnaam tussen beide is geen gedeelde opslag.

## Component en plaatsing

Het SDC-component staat in `web/themes/custom/ksa/components/hero/`.
De Home-pagina `/home` (node 39) bevat één Hero via Layout Builder.
De sitevoorpagina is nog niet gewijzigd: dit is de eerste gebouwde sectie.
De intro en bestaande groepsfoto’s zijn voorbeeldinhoud.

Via **Home → Layout → Hero configureren** pas je de inhoud aan.
Bewaar eerst het blok en daarna de layout.

- Titel en intro: gewone tekstvelden.
- Foto’s: één tot vier afbeeldingen uit de Media-bibliotheek.
- Achtergrond (`field_background`): optionele Media-afbeelding, maximaal één.
- Achtergrond leeg: de originele Figma-illustratie uit het thema.

Het achtergrondveld is via de admin-UI aangemaakt. Er was geen passend
bestaand veld om te hergebruiken. De algemene naam is bruikbaar voor andere
bloktypes met dezelfde veldopslag: één Media-afbeelding.

De widget is Media Library. De achtergrond gebruikt Rendered entity met de
Media-weergave **Hero-achtergrond**: responsive (800, 1440, 2560 breed, AVIF), `eager`.
De foto's gebruiken Media Thumbnail met stijl **Herofoto** (480 × 518). Zie `beeldstijlen.md`.
De achtergrond is decoratief en wordt niet aan schermlezers voorgelezen.
Kies een brede, rustige afbeelding waarop witte tekst leesbaar blijft.

## Weergave

Drupals gerenderde velden gaan via slots naar het component.
Het bloktemplate koppelt de velden; een veldtemplate maakt de fotokaarten.
Geen entity-query’s in Twig. Geen preprocessors of extra modules.
De paginatitel blijft H1; de hero gebruikt H2.

De standaardillustratie is `Ksa_header` uit Figma, gespiegeld zoals het ontwerp.
Bron: bestand `fjlPw9tE1afoSAoD7PBjy0`, laag `44:402`, origineel 2013 × 663.
Bestand: `web/themes/custom/ksa/assets/ksa-header.png`.
De kleuren, Inter, titelgroottes en rondingen gebruiken de gedeelde tokens.
De donkere laag is `#1e1e1e` op 20%, ook bij een eigen achtergrond.
Een gekozen achtergrond wordt niet gespiegeld.

Mobiel toont de waslijn twee kaarten waar dat past; op 320 px één kaart.
De hoogte mag meegroeien met langere tekst en de extra timerbediening.
Knoppen hebben minimaal een klikvlak van 44 × 44 px.

## Beweging

- Automatisch één kaart doorschuiven per vijf seconden.
- Ronde timer toont de resterende tijd.
- Hover of toetsenbordfocus op een foto pauzeert de klok; de foto wiebelt.
- Na verlaten loopt de resterende tijd verder.
- Een ronde pauzeknop linksonder en horizontaal swipen zijn beschikbaar. Er zijn geen pijlen.
- Bij één foto blijft de foto staan. Zonder Jos verdwijnt dan de pauzeknop;
  met Jos blijft hij zichtbaar, want Jos beweegt wel.
- Bij minder-bewegenvoorkeur staat automatisch afspelen uit en zijn animaties uit.
- Zonder JavaScript blijven de originele kaarten horizontaal scrollbaar.
- Drupal behaviors en `once` ondersteunen Layout Builder; detach ruimt timers,
  events en de ResizeObserver op.

De demo `/prototypes/hero/` gebruikt dezelfde CSS en JavaScript als Drupal.
Alleen de voorbeeldinhoud en demoknoppen zijn apart. De demo kan één tot acht
foto’s testen; het Drupal-veld blijft bewust maximaal vier.

## Controle

- Drupal-rendering via Layout Builder gecontroleerd.
- Achtergrond gekozen via Media Library: originele afbeelding en donkere laag correct.
- Daarna de tijdelijke testkeuze verworpen; de KSA-illustratie blijft actief.
- Desktop 1280 px, mobiel 402 px en 320 px gecontroleerd.
- Op 320 px geen horizontale pagina-overloop; knoppen minimaal 44 px hoog.
- Eén foto, pauzeren en hernemen gecontroleerd in de gedeelde preview.

## Afwerking navigatie en fotokaders

De hero-achtergrond loopt onder de transparante navigatie. Logo en tekst
zijn daar wit; het mobiele menupaneel behoudt zijn donkere tekst en logo.
De bestaande H1 blijft beschikbaar voor schermlezers. Layout Builder
behoudt zijn gewone bewerkingsweergave.

De waslijn reserveert extra ruimte voor gedraaide kaarten (ook bij hover).
Het aantal zichtbare kaarten houdt rekening met die ruimte. Verborgen
herhalingen zijn buiten de schuifanimatie ook visueel verborgen.

## Jos de vos

Sinds 29 september 2026 staat Jos de vos onderaan in de hero.
Het component staat apart in `components/jos/`; uitleg in `docs/jos.md`.

- `hero.twig` voegt Jos toe vóór de fotokaarten.
- `hero.css` bepaalt zijn plaats en grootte.
- `hero.js` zet `.is-paused` op de hero; Jos pauzeert dan mee.

Kopie van de hero van vóór Jos: `backups/2026-09-29-voor-jos/`.
