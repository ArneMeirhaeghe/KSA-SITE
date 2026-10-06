# Uitvoering sitescan — 5 oktober 2026

Status: relevante siteverbeteringen uitgevoerd en lokaal gecontroleerd op 6 oktober 2026. Zie de bijgewerkte to-dolijst voor alternatieve oplossingen en expliciete uitstelpunten.

## Uitgevoerd

- Via Drupal-beheer: redacteursrechten beperkt tot teksten, ploegen, leiders en media volgens de keuze van Arne; geen uitbreiding voor layout-, blok-, agenda- of gebruikersbeheer.
- Via Drupal-beheer: 31 leiders met actuele publieke contactgegevens en individuele foto's, zeven leeftijdsploegen met gecorrigeerde informatie, hoofdleiding bijgewerkt en de niet-actuele +16-ploeg gedepubliceerd. Telefoonnummers gebruiken internationaal formaat. Babysitting is niet aangezet zonder bron voor toestemming.
- Via Drupal-beheer: drie nieuwsberichten met echte inhoud en affiches, herkenbaar als archiefberichten; Nederlandse foutpagina en aanvullende interfacevertalingen.
- Via Drupal-beheer: Layout Builder Styles ingeschakeld en expliciete sectiestijlen toegepast op stappen, praktische tabbladen, rondeboekjes en tekst naast foto. Route-link bij Verhuur toegevoegd.
- Thema: gedeelde opmaak voor wachtwoordherstel, afgewerkte leiderpagina's en teruglinks, begrensde tooltip, zichtbare mobiele tabbladen, stapteller, inklapbare maandkalender en expliciete primaire headeractie. Opruiming toegevoegd aan vier JavaScriptcomponenten.
- Agenda: annuleringen weggefilterd na herhalingsuitbreiding; dagwisselmiddleware vóór Drupal Page Cache; laatste succesvolle synchronisatie en fouten zichtbaar in instellingen.
- Git geïnitialiseerd met uitsluitingen; configuratiearchief via Drupal-beheer geëxporteerd naar `config/sync`; documentatie bijgewerkt. Een eerste lokale commit is op 6 oktober gemaakt; niets is gepubliceerd.

## Controles

- Vijf agenda-controlescripts: 58 van 58 controles slagen (19 parser, 8 dagwissel, 7 datumlabel, 17 kalender-ID, 7 ICS).
- De dagwisseltest is een geïsoleerde middlewaretest met testobjecten; geen volledige Drupal Page Cache-integratietest.
- Syntaxcontrole van text-media, site-header, media-strip en jos-login geslaagd.
- Anoniem wachtwoordherstel op 320 pixels: Nederlands formulier, geen horizontale overflow (scrollWidth 320). Geen e-mail verstuurd.
- Nederlandse foutpagina op 320 pixels: leesbare opmaak, drie herstel-links, geen horizontale overflow. Screenshot: `qa/404-mobile.jpg`.
- Zeven door Arne hernoemde groepsfoto's via de Media-dialoog geüpload en opgeslagen: dolfijnen → Dolfijntjes; Leeuwkesdeinze → Leeuwkes Deinze; leeuwkesastene → Leeuwkes Astene; JoroDeinze → Joro Deinze; JoroAstene → Joro Astene; KNIM → Knim; SJO → Sjo. Bron: `/Users/arne/Downloads/ksa/groepsfotos`.
- Groepskaarten tonen de volledige foto met `object-fit: contain`, zodat portretfoto's geen groepsleden afsnijden. Desktop op 1440 pixels en anonieme mobiele weergave op 390 pixels gecontroleerd; alle acht kaartbeelden laden en mobiel geen horizontale overflow. Bewijs: `qa/groepsfotos-desktop.jpg` en `qa/groepsfotos-mobile.jpg`. Hoofdleiding behoudt het taklogo.

## Aanvullend uitgevoerd op 6 oktober

- Sitenaam en SVG-logo/favicon via de thema- en site-instellingen aangepast. De originele vector komt uit `Downloads/ksa/Ksa_Logo.svg`; de favicon gebruikt een donkere variant.
- Pathauto-patronen via beheer aangemaakt en bulkactie uitgevoerd voor 3 nieuwsberichten, 9 ploegen en 31 leiders. Bestaande node-URL's blijven werken.
- Metatag en Open Graph geïnstalleerd; via beheer algemene, homepage- en inhoudstypepatronen ingesteld. Homepage canonical wijst naar `/`. Nieuws gebruikt de affiche, ploegen hun groepsfoto en leiders hun portret. Basis-pagina's delen de KSA-header.
- Hoofdletters van ploegen in Ons verhaal gecorrigeerd via Layout Builder. De gedeelde rondetekst kreeg een link naar `/rondes#agenda`. Verhuur kreeg een bewerkbare h2 boven de kenmerken.
- Eén zichtbare h1 op de homepage, Nederlandse hoofdnavigatie en voetnavigatie, actieve Leiding-link op ploeg- en leiderdetails.
- Donkerder foto-overlay, beter leesbare boekingsnotitie, compactere mobiele contactkaarten, links uitgelijnde routetekst en gedeelde foutkleurtokens.
- Weekdagen in kalenderdatums, kop Komende activiteiten, kalendericoon, standaard gesloten maandkalender en groene Google-kalenderkleur. De daadwerkelijk gerenderde Google-afspraak is rgb(40, 125, 39).
- Nieuws-pager met Vorige, paginanummers en Volgende op basis van Drupal-core. Met drie huidige nieuwsberichten is geen tweede nieuwspagina zichtbaar; daarvoor is geen testinhoud toegevoegd.
- Headerafbeelding van 168 naar 21 KB (WebP), Inter van 337 naar 170 KB met Latijnse tekens, SVG-logo circa 12 KB. Originele bestanden blijven beschikbaar. Geen netwerkbenchmark uitgevoerd.
- Footerafstand samengebracht in één token van 64 px; gemeten 64–65 px tussen main en footer op zeven pagina's. Bestaande nuttige componentselectoren blijven staan; geen volledige vervanging van alle `:has()`-regels geclaimd.

## Eindcontrole op 6 oktober

- Alle 50 gepubliceerde nodes via de browser gecontroleerd op 320 en 1440 px: één h1, geen gevonden runtimefout, horizontale overflow, zichtbare testtekst of kapotte geladen afbeelding. Mobiele telefoonnummers bevatten geldige internationale nummers, met Drupals URL-gecodeerde plus. Er zijn geen echte telefoongesprekken gestart.
- Zeven hoofdpagina's daarnaast gecontroleerd op 390, 768 en 1920 px. Ook wachtwoordherstel en 404 mobiel gecontroleerd. Screenshot- en JSON-bewijs staat in `docs/qa/`.
- Alle zes inschrijvingsstappen, het laatste praktische tabblad, mobiel menu/Escape met focusherstel en hero-pauzeknop werken. Agenda-pagina 2 toont andere activiteiten; maandkalender opent en laadt echte Google-inhoud.
- Zeven PDF's: HTTP 200 en geldige PDF-inhoud. Eén activiteitendownload: HTTP 200 en geldige VCALENDAR/VEVENT-inhoud.
- 58 agenda-controles en 21 toegangscontroles geslaagd. De toegangscontrole gebruikt een tijdelijk UserSession-object, geen opgeslagen testaccount of volledige browserlogin als redacteur.
- De actieve container bevestigt AgendaDay → Drupal PageCache. Dit vult de geïsoleerde dagwisseltest aan; er is geen klokwijziging op de site uitgevoerd.
- PHP-syntax, de vier aangepaste JavaScriptbestanden en Composer-configuratie gecontroleerd.
- 411 configuratiebestanden geëxporteerd via Drupal-beheer en opgeslagen in `config/sync`.

## Herstel en versiebeheer

De broncode, configuratie en controledocumentatie staan in een lokale Git-commit. De werkmap is schoon. De eindbackup staat in `backups/2026-10-06-na-siteverbeteringen/` en bevat de database, publieke bestanden en een broncodearchief. Backups en lokale credentials zijn uitgesloten van Git.

## Bewuste grenzen

Redacteurs krijgen volgens Arnes keuze geen agendabeheer, layouts, gebruikersbeheer of verwijderrechten. Rabbit Hole is overbodig door de verzorgde leiderdetailpagina's. PHPUnit, automatische kampkaarten, de Google-geheimlinkreset en onderzoek naar een andere sessie maken geen deel uit van deze afgeronde verbeteringsronde. Niet alle beheerlabels zijn vertaald. Er zijn geen testmails, betalingen of inschrijvingen verstuurd, en geen fysieke mobiele toestellen getest.

Bron voor overgenomen openbare inhoud: https://ksadeinze.be. Er is niets gepusht of gedeployd.
