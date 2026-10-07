# SEO-onderzoek KSA Deinze-Astene

Datum: 6 oktober 2026. Onderzoek, nog geen wijzigingen aan de website of Drupal-instellingen.

## Conclusie

De nieuwe site heeft een bruikbare technische basis. De grootste winst ligt in duidelijke lokale informatie voor nieuwe ouders, het behouden van bestaande vindbaarheid bij de migratie en het meetbaar maken van bezoeken die tot een inschrijving leiden. Een extra SEO-module alleen lost dit niet op.

Richt de site op **KSA Deinze**, **KSA Deinze-Astene**, **jeugdbeweging Deinze** en **jeugdbeweging Astene**. Dit zijn voorgestelde zoekintenties, geen gemeten zoekvolumes of bewezen rankings. Houd verhuur als aparte behoefte herkenbaar.

## Onderzoeksbasis en grenzen

- Anonieme HTTP-scan van 50 lokale inhoudsroutes en hun 50 canonical-adressen, plus homepagevarianten, oude URL’s, sitemap, robots en een niet-bestaande route.
- Actieve Drupal-modules alleen-lezen gecontroleerd; niet uitsluitend op de opgeslagen configuratie vertrouwd.
- Publieke bestaande site, zoekresultaten en officiële verwijzing van Stad Deinze bekeken.
- Technische richtlijnen getoetst aan Google Search Central.
- Geen toegang tot Search Console, statistieken, Google Bedrijfsprofiel of serverlogs. Geen volledige backlinkanalyse, volledige inventaris van de oude site of representatieve mobiele snelheidsmeting uitgevoerd. Zoekresultaten zijn een steekproef, geen Google-rankingsrapport.
- De lokale `.ddev.site` is de nieuwe ontwikkelversie; conclusies over de openbare oude site staan apart. Een lokaal correct resultaat bewijst geen correcte productieconfiguratie.

Bewijs: [HTTP-scan](qa/seo-audit-2026-10-06.json) en [controle canonical-adressen](qa/seo-alias-check-2026-10-06.json).

## Wat goed staat op de nieuwe site

Alle 50 gecontroleerde inhoudsroutes en alle 50 canonical-adressen geven HTTP 200. Ze hebben een paginatitel, één H1, Nederlandse taalmarkering en een canonical. De inhoud en metadata zijn in de server-HTML beschikbaar. De `/node/...`-routes verwijzen met hun canonical naar leesbare aliassen; de homepagevarianten verwijzen naar `/`.

Metatag en Open Graph zijn actief. Nieuws en ploegen hebben eigen beschrijvingen. In de gecontroleerde HTML ontbreekt bij geen enkel `img` het alt-attribuut; de inhoudelijke kwaliteit van alle alternatieve teksten is hiermee niet beoordeeld. Een echt niet-bestaand adres geeft correct HTTP 404.

## Prioriteiten vóór lancering

| Prioriteit | Gecontroleerde bevinding | Actie |
|---|---|---|
| Hoog | Oude paden `/inschrijven`, `/werking`, `/data` en `/uniform` geven lokaal 404. | Volledige URL-inventaris maken en relevante permanente redirects instellen vóór de overstap. |
| Hoog | De publieke homepage bevat onder meer Cavabar 2026, Quiz 2026 en oudere berichten; de nieuwe versie bevat momenteel drie nieuwsberichten. | Beslissen welke artikelen en documenten overgaan. Waardevolle inhoud en bestaande links behouden. |
| Hoog | De lokale homepage noemt in de titel en beschrijving geen “jeugdbeweging”. De titel is nu “KSA Deinze-Astene — Samen spelen en avonturen beleven” (met verticale scheiding in HTML). | Lokale positionering expliciet maken in titel en een korte zichtbare introductie. |
| Hoog | `/sitemap.xml` geeft 404; Simple XML Sitemap, XML Sitemap en Redirect zijn niet actief. | Een onderhouden sitemapoplossing en redirectbeheer voorzien. Een module is een middel; serverredirects kunnen ook. |
| Hoog | `/?page=1` geeft 200 met canonical naar `/`, terwijl de nieuwssectie daar leeg is met de huidige drie berichten. | Voor echte vervolgpagina’s een eigen canonical met paginaparameter voorzien. Lege pagina’s boven het bereik afhandelen. Verifiëren zodra er meer dan zes artikelen zijn. |
| Midden | `/ons-verhaal` mist een metabeschrijving. Verhuur gebruikt de hele lange introductie als beschrijving. | Unieke, korte beschrijvingen per hoofdpagina instellen. |
| Midden | Geen JSON-LD gevonden op de gecontroleerde pagina’s. | Klopbare Organization- en WebSite-gegevens toevoegen; eventueel Article op nieuws. |
| Midden | `/node/51` is rechtstreeks als gewone 200-pagina bereikbaar, zonder noindex. | De foutpagina zelf uitsluiten van indexering/sitemap; echte fouten moeten 404 blijven. |
| Midden | `/home` en `/node/39` blijven 200 met correcte canonical naar `/`. | Naar `/` doorverwijzen is een nette consolidatie; minder dringend dan kapotte oude links. |

Een sitemap helpt ontdekking en monitoring, maar is geen garantie op indexering. Canonicals zijn signalen; Google bepaalt uiteindelijk het voorkeursadres. [Google: sitemaps](https://developers.google.com/search/docs/crawling-indexing/sitemaps/build-sitemap), [canonicals](https://developers.google.com/search/docs/crawling-indexing/consolidate-duplicate-urls).

Voor paginatie moeten vervolgpagina’s zelfstandig bereikbaar zijn via gewone links en hun eigen canonical behouden. [Google: paginatie](https://developers.google.com/search/docs/specialty/ecommerce/pagination-and-incremental-page-loading).

## Inhoud die ouders helpt kiezen

Voorgestelde eerste tekst op de homepage:

> KSA Deinze-Astene is een jeugdbeweging voor kinderen en jongeren uit Deinze en Astene. Samen spelen, nieuwe vrienden maken en op kamp gaan: ontdek onze leeftijdsgroepen, activiteiten en hoe je kunt aansluiten.

Daaronder of op de inschrijfpagina moeten ouders snel vinden: vanaf welke leeftijd hun kind welkom is, welke groep past, waar en wanneer de rondes plaatsvinden, het actuele lidgeld, hoe kennismaken werkt, wat je meebrengt en hoe inschrijven gaat. Link rechtstreeks naar de juiste ploeg en de inschrijving. Controleer leeftijd, uren en prijs voor publicatie; kopieer die niet blind uit historische teksten.

De openbare oude site vermeldt €35 op [Werking](https://ksadeinze.be/werking) en €37 op [Inschrijven](https://ksadeinze.be/inschrijven). Dit is een concrete inconsistentie die bij de migratie moet verdwijnen. Kies één actuele bron voor praktische gegevens.

| Pagina | Voorgestelde SEO-titel | Belangrijkste inhoud |
|---|---|---|
| Home | KSA Deinze-Astene — Jeugdbeweging in Deinze en Astene | Wie jullie zijn, voor wie, waar, kennismaken |
| Inschrijving | Inschrijven, lidgeld en praktische info — KSA Deinze-Astene | Stappen, leeftijd, prijs, kennismaking, uniform |
| Rondes | Activiteiten en kampen in Deinze en Astene — KSA | Agenda, normale werking, groepen, kampen |
| Leiding | Leiding en leeftijdsgroepen — KSA Deinze-Astene | Groep kiezen, verantwoordelijke vinden |
| Ons verhaal | Over onze jeugdbeweging — KSA Deinze-Astene | Werking, geschiedenis en lokale verankering |
| Verhuur | Jeugdlokaal ’t Brielhof huren in Deinze — KSA | Ligging, faciliteiten, voorwaarden, beschikbaarheid |
| Contact | Contact en locaties — KSA Deinze-Astene | Correcte adressen, route, contact per vraag |

Voorbeeld metabeschrijving home: “Op zoek naar een jeugdbeweging in Deinze of Astene? Ontdek de activiteiten, leeftijdsgroepen en kampen van KSA Deinze-Astene en hoe je kunt aansluiten.”

De zichtbare H1 hoeft niet exact dezelfde tekst als de SEO-titel te krijgen. Bewaar het karakter van de site. Titels moeten specifiek en leesbaar zijn; Google kan titels en snippets anders tonen. [Google: titels](https://developers.google.com/search/docs/appearance/title-link).

De 31 leiderspagina’s zijn vooral nuttig voor bestaande leden. Geef voor werving prioriteit aan de homepage, ploegen en inschrijving. Ga niet automatisch leiderspagina’s verwijderen of noindex geven; beoordeel hun nut en verkeer eerst. Geen kunstmatige tekstlengte of massa vergelijkbare plaatsnaam-pagina’s nodig.

## Migratie: eerste redirectvoorstel

| Oud | Voorgestelde bestemming | Voorwaarde |
|---|---|---|
| `/inschrijven` | `/inschrijving` | Inschrijfinfo volledig aanwezig |
| `/werking` | `/ons-verhaal` | Werking meenemen en naar praktische info linken |
| `/data` | `/rondes` | Alle relevante data overnemen |
| `/uniform` | `/inschrijving` | Uniforminformatie daar werkelijk opnemen |
| `/lokalen` | `/verhuur` | Locatiegegevens en verhuurinhoud behouden |
| `/verzekering` | `/inschrijving` | Verzekeringsinformatie daar aanwezig maken |
| `/blog/detail/startdag-2026` | `/nieuws/startdag-2026` | Bestaand nieuw artikel |
| `/blog/detail/ontbijtmanden-voor-moederdag` | `/nieuws/ontbijtmanden-voor-moederdag` | Bestaand nieuw artikel |
| `/blog/detail/jumpfest-is-terug` | `/nieuws/jumpfest-terug` | Bestaand nieuw artikel |
| `/blog` | Nog te bepalen nieuwsarchief of homepage met volledig archief | Eerst archiefinhoud en paginatie afronden |
| Oude PDF-paden | Hetzelfde document op nieuwe locatie | Per bestand inventariseren |

Dit is een startlijst, geen uitvoerklare volledige mapping. Inventariseer ook oude blogpaginatie, artikelen, verwijzende links en PDF’s. Gebruik 301/308 rechtstreeks naar de relevante bestemming. Stuur niet alle verdwenen berichten naar de homepage. Bewaar redirects minstens een jaar, liefst langer voor blijvende links. [Google: URL-migraties](https://developers.google.com/search/docs/crawling-indexing/site-move-with-url-changes).

## Publieke domeinen en lokale aanwezigheid

De publieke `www`-homepage en de homepage zonder `www` zijn beide als 200 bereikbaar; de canonical wijst naar het domein zonder `www`. Ook `https://ww5.ksadeinze.be/data` staat in de zoeksteekproef en geeft bij directe controle 200, met canonical naar `https://ksadeinze.be/data`. Laat de hostingbeheerder ongewenste varianten naar één HTTPS-voorkeursdomein doorsturen, met behoud van het juiste pad. Dit is geen bewijs van misbruik, wel onnodige duplicatie.

Eén eerste verzoek aan de publieke homepage gaf 500; twee hercontroles gaven 200. Dit is een incidentele waarneming, geen bewezen structurele storing. Controleer serverlogs en beschikbaarheid bij de lancering.

[Stad Deinze linkt al naar KSA Deinze-Astene](https://www.deinze.be/jeugdverenigingen-overzicht). Behoud deze waardevolle lokale verwijzing en controleer ook KSA Nationaal, jeugdraad, sociale profielen en eventuele lokale partners op correcte URL en naam. Maak onderscheid tussen jullie vereniging en andere KSA-groepen in de gemeente.

Controleer een bestaand Google Bedrijfsprofiel op eigendom, juiste categorie, locatie en contactgegevens. Maak alleen een profiel aan als de vereniging aan de voorwaarden voldoet, en voorkom duplicaten. Voorzie geen fictieve openingsuren: ontmoetingsuren en activiteiten verschillen. De status van een bestaand profiel is in dit onderzoek niet vastgesteld. Lokale resultaten hangen onder andere af van relevantie, afstand en bekendheid. [Google: lokale vindbaarheid](https://support.google.com/business/answer/7091?hl=en).

Organization-structured data kan naam, officiële URL, logo en bevestigde sociale profielen verduidelijken. Neem alleen correcte, ook zichtbare gegevens op. Verzin geen reviews of beoordelingen. Event-markup pas overwegen voor echte, inhoudelijke evenementpagina’s; een agenda-embed of ICS-download is geen volwaardige evenementdetailpagina. [Google: Organization](https://developers.google.com/search/docs/appearance/structured-data/organization).

## Snelheid en meten

De site bevat veel foto’s en animatie. Test daarom bij lancering minimaal home, inschrijving, ploeg, nieuws en rondes op mobiel met PageSpeed Insights, en later met echte gebruikersgegevens in Search Console. Beoordeel afbeeldingsformaten en afmetingen, laden van beelden onderaan de pagina, de grootste afbeelding bovenaan, layoutverschuivingen en de invloed van agenda en animaties. Er is nu geen betrouwbare snelheidsscore vastgesteld.

Streef bij echte gebruikers naar LCP ≤2,5 seconden, INP ≤200 ms en CLS ≤0,1. Een lokale snelle laptop is geen bewijs dat bezoekers dit halen. [Google: Core Web Vitals](https://developers.google.com/search/docs/appearance/core-web-vitals).

Vóór livegang: Search Console-domeinproperty verifiëren, beschikbare oude zoekdata bewaren, volledige redirectlijst testen en productie-indexeerbaarheid controleren. Een gedeelde testomgeving afschermen; productie mag geen achtergebleven noindex bevatten. Robots.txt alleen is geen betrouwbare manier om een testsite uit zoekresultaten te houden.

Bij livegang: sitemap indienen, belangrijke URL’s inspecteren, HTTPS en canonical-domein controleren, oude links en echte 404’s testen.

Na livegang: na één week, één maand en drie maanden indexering, 404/5xx, zoekopdrachten, klikken en doorklikratio beoordelen. Splits merkzoekopdrachten van “jeugdbeweging Deinze/Astene”. Meet indien passend ook klikken naar inschrijven en contact; een klik is nog geen voltooide inschrijving. Vergelijk met dezelfde seizoensperiode waar historische data beschikbaar is.

De aanbevolen volgorde is: migratie en praktische informatie → lokale titels en teksten → sitemap en indexering → lokale profielen en structured data → meten en bijsturen. Geen betrouwbare positiegarantie of exact bezoekersresultaat mogelijk zonder nulmeting.


## Uitvoering op 7 oktober 2026

De lokale verbeteringen, voor-en-na-tabel, controles en resterende livegangspunten staan in [SEO-uitvoering](seo-uitvoering-2026-10-07.md). Bovenstaande bevindingen beschrijven de situatie tijdens het onderzoek.
