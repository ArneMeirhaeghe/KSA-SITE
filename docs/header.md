# Header

## Vergroting (6 oktober 2026)

Op verzoek is de navigatie groter dan de oorspronkelijke Figma-maten:
logo 150 px breed, menutekst en acties 14 px, knoppen met 8 px verticale
padding. Het desktopbreakpoint blijft 1120 px. Mobiel behoudt de
hoofdnavigatie in het open paneel zijn grotere koptekst.
Gecontroleerd op 320, 402, 1120, 1440, 1920 en 2560 px zonder horizontale
overloop; het mobiele menu opent en sluit met Escape en herstelt focus.

Status: donkere navigatie afgestemd op Figma, responsive en werkend.
De witte homepagevariant wacht op de hero.

## Verdeling

- Drupal beheert logo, menu-items, links en blokvolgorde.
- `region--header.html.twig` geeft de gerenderde blokken aan de SDC door.
- `site-header` verzorgt indeling, CSS en de mobiele menuknop.
- Geen eigen preprocessors of queries.

## Ingesteld via de admin-UI

Het KSA-thema is geïnstalleerd en staat op verzoek van Arne als standaard.

Headerblokken, in deze volgorde:

1. Logo: alleen het logo, zonder extra sitenaam of slogan.
2. Hoofdnavigatie: Leiding, Rondes, Inschrijving & info, Ons verhaal, Verhuur.
3. Headeracties: Contacteer ons, Inschrijven.

Home is uitgeschakeld in het menu; het logo linkt naar de homepage.
Foto’s blijft buiten scope. De zes doelpagina’s zijn leeg en gepubliceerd
op deze lokale ontwikkelsite, op verzoek van Arne. URL’s en werkwijze:
`docs/navigatie.md`.

Het logo staat ingesteld op `themes/custom/ksa/logo.png`.
Het is het originele transparante bestand uit Figma, 1417 × 442 px.
Bron: `Ksa_Logo_Transp` in Navbar, desktop Home, node `47:357`.
Het witte monochrome logo krijgt op een witte header een zwarte CSS-filter.
Het bronbestand is ongewijzigd; er wordt geen exportvoorbeeld met schaakbord gebruikt.

Bij installatie plaatste Drupal ook titels en beheerblokken in de header.
Die staan nu in Inhoud. Meldingen en tabs komen voor de paginatitel en inhoud.
Accountmenu en Powered by Drupal zijn alleen voor het KSA-thema uitgeschakeld.
Footerblokken en footermenu zijn niet ingevuld: die zijn nog placeholders.

## Nog afwerken

- Doelpagina’s vullen met Layout Builder.
- Inschrijven verwijst voorlopig naar Inschrijving & info.
  Een eventuele externe inschrijflink kan later via het menu worden ingesteld.
- Transparante Home-variant met witte tekst pas koppelen wanneer de hero bestaat.
- De tweede actie krijgt de groene knopstijl; volgorde in Headeracties behouden.

## Gecontroleerd

- Echte Drupal-pagina tijdelijk met KSA als standaard gerenderd, daarna hersteld.
- SDC en bloktemplates renderen zonder fout.
- Origineel logo laadt lokaal en toont 129 px breed.
- Bij 402 px opent/sluit Menu; Escape sluit en herstelt focus op Menu.
- Geen horizontale overflow bij 402 px met het huidige menu.
- Bij 1280 px is het menu zichtbaar zonder uitklapknop.
- JavaScript-syntax gecontroleerd met `node --check`.
- Volledig menu met beide acties gecontroleerd op 1280 px en 402 px.
  Geen horizontale overflow. Mobiel openen, sluiten en Escape werken.

Zonder JavaScript worden de menu’s door de CSS niet ingeklapt.
Dit is nog niet apart in een browser met JavaScript uit getest.

## Responsive verfijning (28 september 2026)

Gemeten via Figma Web: desktop Navbar Dark in Contact (`107:2602`),
Navbar Light (`47:357`), Navbar-Mobile Light (`121:1790`) en de donkere
Navbar-Mobile in Blog Detail op de mobiele ontwerppagina.

- Desktop: container 1080 px, logo 129 × 40,24 px, bovenmarge 54 px.
- Menu: Inter 12 px, 24 px tussen links; groepen verdeeld met space-between.
- Acties: 12 px tussen knoppen; padding 6 px verticaal / 12 px horizontaal;
  straal 4 px; zichtbare hoogte 27 px. De rand staat binnen de knop.
- Mobiel Dark: buitenmarge 16 px, bovenmarge 21 px, balk 40,24 px hoog.
- Mobiel Light: bovenmarge 24 px en 16 px binnenruimte; pas toepassen met hero.
- Hamburger: 24 px groot met een onzichtbaar klikvlak van 44 px.
  Met toestemming van Arne nagemaakt in CSS omdat de originele SVG-export
  en Figma MCP niet beschikbaar waren. Open toestand toont een kruisje.
- Breakpoint 1120 px is een implementatiekeuze.
- De open toestand volgt op verzoek van Arne het gedrag van Sitetrip:
  een schermvullende overlay met een paneel dat van rechts binnenkomt.
- Foto’s blijft volgens scope weg; daardoor verschilt de menu-inhoud van Figma.
- Desktop Contact staat in Figma 2 px uit het midden (x=98).
  De website gebruikt de gedeelde, gecentreerde container (x=100 bij 1280 px).

Gecontroleerd bij 320, 402, 768, 1120 en 1280 px: geen horizontale overflow.
Logo is 129 px; gesloten balk 40,234 px; desktopknoppen 27 px hoog.
Mobiel openen, sluiten met Escape en terugkerende focus zijn gecontroleerd.
De Drupal-beheerbalken zijn niet onderdeel van het bezoekersontwerp.

## Schermvullend mobiel menu

Referentie: https://sitetrip.be/nl (mobiel bekeken op 28 september 2026).
KSA behoudt eigen kleuren, Inter en afrondingen uit de tokens.
Openen duurt 0,7 seconde met een snelle start en zachte vertraging.
Sluiten duurt 0,35 seconde; de achtergrond vervaagt in 0,25 seconde.
Rond het paneel blijft 8 px zichtbaar;
daarachter staat een donkere overlay over het volledige scherm.

Een native HTML `dialog` plaatst het menu boven de pagina. JavaScript
verplaatst de bestaande Drupal-blokken tijdelijk naar dit dialoogvenster.
Bij sluiten gaan ze terug. Er is dus één set menulinks om te beheren.
Geen extra module en geen preprocessor.

- De achtergrond kan niet scrollen of aangeklikt worden tijdens het openen.
- Kruisje, Escape, menulink of klikken op de buitenrand sluiten het menu.
- Tab en Shift+Tab blijven binnen het menu; sluiten herstelt focus.
- Bij omschakelen naar desktop herstelt de gewone navigatie.
- Het paneel kan zelf scrollen als de inhoud hoger is dan het scherm.
- `prefers-reduced-motion` schakelt de overgang uit.

Gecontroleerd: 402 × 820, 320 × 568 en omschakelen naar 1280 px.
Focuslus, Escape, scrollblokkering en het terugplaatsen van blokken werken.
De verminderde-bewegingsinstelling is in CSS/JS voorzien, niet via de
OS-instelling getest.

### Herstel van de haperende overgang

Autofocus scrolde het dialoogvenster horizontaal naar de sluitknop terwijl
het paneel nog buiten beeld stond (gemeten `scrollLeft: 151.5`).
`overflow: clip` voorkomt dit; tijdens de nieuwe overgang blijft dit 0.
De menuknop blijft rechts staan wanneer de blokken worden verplaatst.
De startpositie wordt direct vastgelegd; de dubbele framewachttijd is weg.
