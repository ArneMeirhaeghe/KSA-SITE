# Audit gebruiksgemak en SEO — 16 november 2026

Nagekeken vóór de livegang. Lokaal op `ksa.ddev.site`, online op de testsite.

Dit rapport beschrijft de toestand **vóór** de wijzigingen. Tijdens de audit is niets gewijzigd.
Wat daarna is uitgevoerd, staat in [audit-uitvoering-2026-11-17.md](audit-uitvoering-2026-11-17.md).

Cijfers als bewijs: [qa/audit-gebruiksgemak-seo-2026-11-16.json](qa/audit-gebruiksgemak-seo-2026-11-16.json).

## In het kort

De basis is goed. Elke pagina heeft één H1, een eigen beschrijving,
alt-teksten, moderne beeldformaten en een menu dat met het toetsenbord werkt.

Zeven dingen moeten eerst:

1. **Beveiligingsupdates.** Eén module heeft een kritiek lek. Valt buiten je vraag, maar blokkeert de livegang.
2. **De pagina verspringt bij het laden.** Op vijf van de negen gemeten pagina's.
3. **50 redirects staan klaar, maar zijn niet aangemaakt.**
4. **De sitemap bevat pagina's die er niet in horen.** Onder meer 31 leiderpagina's met naam en gsm-nummer.
5. **Deelfoto's zijn te zwaar.** Tot 3,3 MB. WhatsApp toont dan vaak geen voorbeeld.
6. **Ouders vinden de juiste groep niet vlot.** Leeftijdsgroepen zitten onder "Leiding".
7. **De inhoud is niet actueel.** Oude boekjes, geen activiteit vóór 23 januari.

## Wat ik heb nagekeken

| Wat | Hoe | Waar |
|---|---|---|
| Alle pagina's | Eigen crawler, alleen lezen: 93 adressen (69 pagina's, 16 agenda-downloads, 7 pdf's, 1 redirect) | lokaal |
| Snelheid en kwaliteit | Google PageSpeed Insights (Lighthouse 13.6), 9 pagina's, gsm en desktop | testsite |
| Nulmeting | PageSpeed op de huidige `ksadeinze.be` | oude site |
| HTML | W3C Nu HTML Checker | testsite |
| Toegankelijkheid | WAVE (WebAIM), 2 pagina's | testsite |
| Gestructureerde gegevens | Schema.org-validator, 2 pagina's | lokale code |
| Veiligheid server | Mozilla Observatory | testsite |
| Zoekresultaten | Google: `site:` en "jeugdbeweging deinze" | oude site |
| Schermen | Chrome op 1440 px, ingebouwde browser op 375 px | lokaal |
| Eerste bezoeker | Vijf taken van een nieuwe ouder, alleen op de tekst van de pagina's | lokaal |
| Instellingen | `drush config:get` en `composer audit`, alleen lezen | lokaal |

De grenzen staan onderaan.

## Scores van PageSpeed Insights

Volgorde: prestaties / toegankelijkheid / praktische tips / SEO.
Eén meting per pagina. Zulke labmetingen schommelen een paar punten.

| Pagina | Gsm | LCP | CLS | Desktop | CLS |
|---|---|---|---|---|---|
| Home | 83 / 96 / 100 / 69 | 2,7 s | **0,272** | 85 / 96 / 100 / 69 | **0,303** |
| Inschrijving | 81 / 97 / 100 / 69 | 2,3 s | **0,366** | 78 / 100 / 100 / 69 | **0,596** |
| Rondes | 82 / 92 / 77 / 69 | 0,9 s | **0,366** | 100 / 96 / 77 / 69 | 0,001 |
| Verhuur | 78 / 96 / 100 / 69 | 2,9 s | **0,366** | 100 / 100 / 100 / 69 | 0,001 |
| Ploeg (Dolfijntjes) | 82 / 96 / 100 / 69 | 2,0 s | **0,366** | 100 / 100 / 100 / 69 | 0,001 |
| Leiding | 100 / 96 / 100 / 69 | 1,1 s | 0 | 100 / 100 / 100 / 69 | 0,005 |
| Nieuws | 97 / 96 / 100 / 69 | 1,8 s | 0 | 100 / 100 / 100 / 69 | 0,002 |
| Nieuwsbericht | 98 / 96 / 100 / 69 | 2,3 s | 0 | 100 / 100 / 100 / 69 | 0,001 |
| Contact | 99 / 96 / 100 / 69 | 2,0 s | 0 | 100 / 100 / 100 / 69 | 0,001 |
| *Oude site, home* | 91 / 91 / 100 / 100 | 1,5 s | 0,189 | 71 / 91 / 100 / 100 | 0,175 |

Zo lees je dit:

- **CLS** meet hoeveel de pagina verspringt. Goed is 0,1 of lager. Vet is te hoog.
- **LCP** is het moment waarop het grootste beeld er staat. Goed is 2,5 s of lager.
- **SEO 69** komt alleen door de bewuste `noindex` van de testsite. Op het echte domein valt die weg.

## Wat al goed staat

- Alle 69 pagina's: status 200, één H1, logische koppen, `lang="nl"`, eigen canonical.
- Geen dubbele titels of beschrijvingen op echte pagina's.
- Alle 378 afbeeldingen hebben een alt-tekst en vaste afmetingen. Foto's zijn AVIF.
- Geen link of knop zonder naam.
- Toetsenbord: skiplink, duidelijke focusrand, gsm-menu sluit met Escape en geeft de focus terug.
- 404-pagina in het Nederlands, met echte 404-status en `noindex`.
- Hoofdletters, slash achteraan en `/node/…` sturen netjes door.
- De 24 bestaande redirects werken. De testsite staat niet in Google.
- Gestructureerde gegevens zijn geldig: 0 fouten in de validator.
- Statische bestanden op de testsite: één jaar cache, gzip aan.
- Op gsm (375 px): geen horizontaal scrollen en geen tekst kleiner dan 14 px.

## Bevindingen

Elke bevinding heeft een code. Zo kun je zeggen: "doe L1 en S2".

### V — Veiligheid

**V1 — Kritieke updates** · hoog · terminal

`ddev composer audit` meldt **16 adviezen in 10 pakketten**:

- `single_content_sync` 1.4.18: twee adviezen "Critical".
  Eén is remote code execution (SA-CONTRIB-2026-275). Opgelost in 1.4.19.
- `view_unpublished` 1.4.0: access bypass (SA-CONTRIB-2026-235). Opgelost in 1.5.0.
- Dertien adviezen voor Symfony-onderdelen, gemeld op 4 november. Opgelost in Symfony 7.4.23.
  Die komen mee met Drupal core. Core 11.4.12 bestaat; lokaal draait 11.4.10.
- Volgens `testomgeving-2026-10-08.md` staat Single Content Sync ook aan op de publieke testsite.

Niet nagekeken: of het lek zonder login te misbruiken is, en of core 11.4.12 al Symfony 7.4.23 meebrengt.
Na de update opnieuw `composer audit` draaien.

### L — Laden en snelheid

**L1 — De pagina verspringt bij het laden** · hoog · code

Gemeten: CLS 0,27 tot 0,60. Oorzaak in de code nagekeken.
JavaScript bouwt de pagina om nadat het eerste beeld er al staat:

- **Gsm-menu.** Zonder JS staat het menu open in de kop. `site-header.js` verbergt het daarna. Alles schuift omhoog. Dat is de 0,366 op gsm.
- **Stappen en tabbladen** op Inschrijving. Zes stappen en vijf tabbladen staan eerst onder elkaar. `text-media.js` verbergt er negen. Dat is de 0,596 op desktop.
- **Hero op home.** `hero.js` zet kopieën vóór de eerste foto. De rij schuift op.

Het is een wedloop. Pagina's waar het eerste beeld later kwam, scoren 0.
Op een trage gsm verlies je die wedloop vaker.

Voorstel: de begintoestand in CSS gelijk maken aan de toestand na JS.
Dat kan met `@media (scripting: enabled)`. Zonder JS blijft alles zichtbaar, zoals nu.

```css
/* Same layout before and after JS runs: no jump on load. */
@media (scripting: enabled) and (width < 70rem) {
  .ksa-header > .ksa-header__blocks :is(.ksa-header__menu, .ksa-header__actions) {
    display: none;
  }
}
```

Dit is een schets voor het menu. Stappen, tabbladen en hero volgen hetzelfde idee.
Let op: in de Layout Builder-editor moeten alle blokken zichtbaar blijven.

Verwacht: CLS onder 0,1 en een gsm-score rond 90. Zeker pas na een nieuwe meting.

**L2 — Foto's zijn te groot, of te klein** · midden · Drupal-UI

- Leiding: groepsfoto's laden op 1090 px breed en worden op 343 px getoond. PageSpeed schat **1,2 MB** winst.
- Nieuwskaarten: zelfde stijl `wide` (1090 px). Op home 249 KiB winst.
- Leiderfoto's: de geladen foto is 172 px breed. Op een gsm-scherm oogt dat onscherp.

Voorstel: kaarten een responsive beeldstijl geven. De module staat aan en er bestaan al vijf stijlen.

**L3 — Het grootste beeld wordt laat ontdekt** · midden · code

PageSpeed-controle "LCP-verzoekdetectie" faalt op 8 van de 9 pagina's, op gsm of desktop.
Op home zit de hero-achtergrond in CSS. De browser ziet hem pas na het stijlblad.
LCP op gsm: home 2,7 s en verhuur 2,9 s. Dat is boven de grens.

Voorstel: de hero-achtergrond en het lettertype Inter (174 KB) vooraf laden met `preload`.
Per pagina nog nakijken welk element het grootste beeld is.

**L4 — Elke pagina sleept 87 KB vos mee** · laag · code

Een gewone pagina weegt 100 KB aan HTML (30 KB gezipt). Home 196 KB.
Reden: `jos.svg` zit ingesloten in de voettekst van elke pagina.
Daarin zit ook 7,7 KB onzichtbare C2PA-metadata.

Voorstel: metadata weghalen. Beter: de tekening apart laden, zoals `text-media.js` al doet.
Dan bewaart de browser ze. Verwacht: HTML rond 20 KB.

**L5 — Browsercache voor pagina's staat uit** · midden · Drupal-UI op productie

`cache.page.max_age` is 0, lokaal en op de testsite.
Voorstel voor productie: 15 minuten tot 1 uur (Configuratie → Prestaties).

**L6 — HTML-fouten** · laag · code

W3C: 28 fouten op 5 pagina's, drie soorten:
`div` binnen `span` (19), de C2PA-metadata in de SVG (8), `pathLength` op een cirkel (1).
Browsers tonen alles goed. Opruimen als we toch in die templates zitten.

### S — Vindbaarheid

**S1 — `/home` staat in de sitemap** · hoog · Drupal-UI

De sitemap telt 63 adressen. `/home` stuurt door naar `/`. Dat hoort er niet in.
Voorstel: node 39 uitsluiten, zoals eerder met de foutpagina (node 51).

**S2 — 31 leiderpagina's zijn weespagina's** · hoog · keuze + Drupal-UI

- Ze staan in de sitemap: de helft van alle adressen.
- Geen enkele pagina linkt ernaar. Ik telde 0 links.
- Ze tellen 16 tot 19 woorden: een naam, een foto, een gsm-nummer.
- Wie in Google een naam zoekt, kan zo dat nummer als resultaat krijgen.

Voorstel: `noindex` en uit de sitemap. De ploegpagina toont dezelfde gegevens.
Dit is een keuze. Zie "Keuzes voor jou".

**S3 — `/node` toont een tweede nieuwslijst** · hoog · Drupal-UI

Publiek bereikbaar, zonder titel ("| KSA Deinze-Astene"), en indexeerbaar.
Het is de standaard-View `frontpage`.
Voorstel: de paginaweergave uitschakelen. De feed `/rss.xml` blijft bestaan.

**S4 — `robots.txt` noemt de sitemap niet** · midden · composer

Er staat geen `Sitemap:`-regel in. Het bestand komt uit de Drupal-scaffold.
Een gewone aanpassing wordt dus overschreven bij een update.
Voorstel: toevoegen via `composer.json` (append), met het echte domein.

**S5 — Titels missen zoekwoorden** · midden · Drupal-UI

De H1's volgen het ontwerp. Dat is goed. Maar de titel in Google volgt mee.
Het metatagveld op basispagina's bestaat al. Alleen de titel aanpassen, niet de H1.

| Pagina | Titel nu | Voorstel |
|---|---|---|
| Leiding | Maak kennis met de leiding | Leiding en leeftijdsgroepen |
| Rondes | Activiteiten & kampen | Rondes, activiteiten en kampen |
| Ons verhaal | Ons verhaal | Over onze jeugdbeweging |
| Ploeg | Knim | Knim: 1ste en 2de middelbaar |
| Nieuws, eerste pagina | Nieuws — pagina 1 | Nieuws |

Achter elke titel komt " | KSA Deinze-Astene".
Voor de ploegen kan dat in één patroon: `[node:title]: [node:field_age_range]`.

**S6 — Gestructureerde gegevens aanvullen** · laag · Drupal-UI

Geldig, maar mager. Google raadt meer aan:

- Organisatie: logo, adres, e-mail, alle sociale profielen.
- Nieuwsbericht: afbeelding, auteur en uitgever.

**S7 — Deelfoto's zijn te zwaar** · hoog · Drupal-UI

De deelfoto (`og:image`) is het originele bestand.
Zeven zijn zwaarder dan 600 KB. De affiche van Startdag 2026 weegt 3,3 MB.
Eén affiche is 11520 px breed. `/nieuws` heeft helemaal geen deeltags.

Dit telt, want jullie links gaan rond in WhatsApp-groepen.

Voorstel: een beeldstijl "delen" van 1200 px breed, als JPEG.
De andere stijlen maken AVIF; deelplatformen lezen dat niet betrouwbaar.
Te testen met een echte link in WhatsApp.

**S8 — Favicon alleen als SVG** · laag · code

`/favicon.ico` en `/apple-touch-icon.png` geven 404.
Voorstel: een `.ico` en een icoon van 180 px toevoegen.

**S9 — Redirects** · hoog · Drupal-UI

- 24 redirects zijn actief en werken.
- De 50 uit `redirects-livegang-2026-11-05.csv` zijn **nog niet aangemaakt**.
- Vandaag bevestigd: `/blog?page=3` → `/nieuws?page=3` → 404.
  Oorzaak: de module Redirect plakt `?page=3` weer achter het doel. Het doel wordt daarom `/nieuws?page=0`.
- Nieuw: `/ploegen` geeft 404. Voorstel: doorsturen naar `/leiding`.

Google kent ongeveer 130 adressen van de oude site, zoals `/blog`, `/lokalen` en `/werking`.

**S10 — Lokale vindbaarheid** · na livegang

Voor "jeugdbeweging deinze" stond de oude site op plaats 8.
Daarboven: `deinze.be` en `jeugdraaddeinze.be`.
Dat is één momentopname in jouw ingelogde Chrome. Geen rangschikkingsrapport.

Voorstel: nakijken of die twee sites, KSA Nationaal en Facebook naar het juiste adres linken.
En een Google Bedrijfsprofiel controleren of aanvragen.

### G — Gebruiksgemak

**G1 — Welke groep past bij mijn kind?** · hoog · inhoud + Drupal-UI

- De leeftijdsgroepen staan op de pagina "Leiding". Een nieuwe ouder zoekt daar niet.
- Een kind uit het 3de leerjaar past bij Leeuwkes Deinze (1ste t.e.m. 3de) én bij Leeuwkes Astene (3de en 4de).
  Dat klopt, want Deinze en Astene zijn anders ingedeeld. Maar dat staat nergens uitgelegd.

Voorstel: een blok "Welke groep?" op de startpagina.
Eerst de plaats kiezen, dan het leerjaar. Zeven kaarten die naar de ploeg linken.

**G2 — De ploegpagina is kaal** · hoog · Drupal-UI

Een ploegpagina toont alleen leiders en een mailadres: 28 tot 47 woorden.
De velden leeftijd, plaats en beschrijving bestaan en zijn ingevuld.
Ze staan verborgen in de weergave. De beschrijving zit wel in de metatag.

Voorstel: die drie velden bovenaan tonen via Layout Builder.

**G3 — Op gsm staat "Inschrijven" niet in beeld** · midden · code

Op desktop staan er twee knoppen in de kop. Op gsm zitten ze in het menu.
De hero heeft geen eigen knop.
Voorstel: op gsm een knop "Inschrijven" onder de introtekst.

**G4 — Tikdoelen zijn klein** · midden · code

Gemeten op 375 px:

| Onderdeel | Nu | Richtlijn |
|---|---|---|
| Links in de voettekst | 17 px hoog | minstens 24 px, liefst 44 |
| Telefoonnummers van leiders | 24 px hoog | 24 px is het minimum; bewuste keuze in het ontwerp |

PageSpeed keurt de voettekstlinks af op elke pagina.
De links in het gsm-menu zijn wel goed: 44 px.

Correctie van 17 november: de menuknop en de sluitknop stonden hier eerst als 24 × 24 px.
Dat was fout gemeten. Het icoon is 24 px, maar het klikvlak is al 44 px (`site-header.css`).
Alleen de voettekstlinks moesten dus groter.

**G5 — Antwoorden die ontbreken** · midden · inhoud door leiding

Niet gevonden op de negen hoofdpagina's:

- Hoe meld je dat je kind niet kan komen?
- Mag een kind eerst komen proberen? Er staat alleen "spreek vooraf af".
- Wat kost een weekend of kamp?
- Verhuur: de prijs, en een contact op de verhuurpagina zelf.
- De adressen van de lokalen op de contactpagina.
- Hoe word je leiding? Nu één zin.

**G6 — Nieuws is moeilijk te vinden** · laag · Drupal-UI

"Nieuws" staat niet in het menu en niet in de voettekst. Alleen via de startpagina.
Voorstel: een link in de voettekst.
Daarnaast: een bericht toont de publicatiedatum, niet de datum van de activiteit.

**G7 — De maandkalender van Google** · midden · code

- Hij staat altijd open en laadt mee zodra je in de buurt scrolt.
  Volgens de docs van oktober zat hij achter een knop "Toon maandkalender". Die zie ik niet meer.
- November is leeg.
- PageSpeed meldt een cookie van een derde partij en 244 KiB ongebruikte code.
  Daardoor scoort Rondes 77 op praktische tips.

Voorstel: pas laden na een klik. Jullie eigen activiteitenlijst staat er al boven.

**G8 — Kleine dingen** · laag

- Telefoonnummers staan als `+324…` zonder spaties. Moeilijk leesbaar.
- Het babysit-icoon heeft alleen uitleg bij zweven of focus.
- De cirkel met een cijfer in de hero legt niet uit wat hij doet.
- Het kruimelpad van een ploeg toont alleen "Home", niet "Leiding".
- Het menu zegt "Rondes", de pagina heet "Activiteiten & kampen".

**G9 — Geen privacyverklaring** · midden · inhoud

De site toont namen en nummers van leiders en laadt een Google-kalender.
Er is geen pagina over privacy. Voorstel: één korte pagina, gelinkt in de voettekst.

### T — Toegankelijkheid

**T1 — Witte tekst op de foto is te zwak** · midden · ontwerpkeuze + code

Contrast van witte tekst, gemeten op 1440 px. De norm is 4,5 op 1.

| Tekst | Contrast |
|---|---|
| Menu in de hero | 5,46 ✓ |
| Introtekst in de hero | 4,15 |
| Links in de voettekst | 4,03 |
| Knop "Jos pauzeren" | 3,16 |
| "© 2026" en "Naar boven" | 2,97 |

Ik heb de achtergrond nagerekend uit de foto en de laag van 20%. Dat is een benadering.
Voorstel: de donkere laag in de voettekst verhogen, of een verloop onderaan.
Dat wijkt af van de 20% uit Figma.

**T2 — Tools zien de foto niet** · laag · code

WAVE meldt 8 contrastfouten op home. PageSpeed meldt de menulinks in de hero.
Dat is grotendeels vals alarm.
Ze rekenen met de onderliggende kleur `#5FA95B` (2,87 op 1), niet met de foto.
Het wordt wel echt als de foto niet laadt.
Voorstel: een donkerdere onderliggende kleur.

Tekst op wit is in orde. Ook de groene knop: die heeft donkere tekst.
Eén uitzondering: het grijze "Vorige" in de agenda, als die knop niet actief is.

### I — Inhoud

**I1 — Niet actueel** · hoog · leiding

- Rondes toont **boekje 1**. De oude site heeft al boekje 2.
- De actie Kerstrozen 2026 loopt en staat niet op de nieuwe site.
- De eerstvolgende activiteit in de agenda is 23 januari 2027. Klopt dat?
- Het nieuwste bericht is van 6 oktober.
- Lidgeld: €37, nog te bevestigen.

**I2 — Afzender van de site** · laag · Drupal-UI

Het site-e-mailadres is nog jouw werkadres. Mails over wachtwoorden komen daarvan.

### H — Hosting bij de livegang

Gemeten op de testsite. Op productie opnieuw nakijken.

- `http://` stuurt **niet** door naar `https://`.
- Geen HSTS en geen andere beveiligingsheaders. Mozilla Observatory: D, 35 op 100.
- `robots.txt` met "Disallow: /" en de `noindex`-header moeten weg op productie.
- `www` naar zonder `www`, en het domein in `trusted_host_patterns`.
- Na elke uitrol `drush cr`. Lokaal wees een pagina uit de cache nog naar een oud scriptbestand.
- `web/sites/default/files.zip` (48 MB) niet mee uitrollen.
- Het statusrapport geeft een fout over de sync-map. Zie de code-audit van 20 oktober.

## Plan

Grootte: **S** is een uur, **M** een halve dag, **L** een dag of meer.
Werk in de Drupal-UI doe ik in Chrome, zodat je meekijkt.

### Fase 0 — Veiligheid

| Stap | Wat | Grootte | Klaar als |
|---|---|---|---|
| 0.1 | V1: core en de twee modules bijwerken, lokaal en op test | S | `composer audit` meldt niets meer |
| 0.2 | Single Content Sync uitschakelen zodra de inhoud over is | S | module staat uit op productie |

### Fase 1 — Techniek vóór de livegang

| Stap | Wat | Soort | Grootte | Klaar als |
|---|---|---|---|---|
| 1.1 | L1: verspringen wegwerken | code | M | PageSpeed: CLS onder 0,1 op alle negen pagina's |
| 1.2 | S9: 50 redirects en `/ploegen` aanmaken | UI | M | elke rij: één 301, doel geeft 200 |
| 1.3 | S1, S2, S3: sitemap en indexering | UI | S | sitemap zonder `/home` en leiders; `/node` geeft 404 |
| 1.4 | S4: sitemap in `robots.txt` | composer | S | regel staat er na `composer install` |
| 1.5 | S7: beeldstijl voor delen | UI | S | deelfoto onder 300 KB; voorbeeld zichtbaar in WhatsApp |
| 1.6 | S5: titels | UI | S | crawl toont de nieuwe titels |
| 1.7 | L2: beeldstijlen op kaarten | UI | M | PageSpeed meldt geen beeldwinst boven 100 KiB |
| 1.8 | L3: hero en lettertype vooraf laden | code | S | LCP op gsm onder 2,5 s |

### Fase 2 — Gebruiksgemak vóór de livegang

| Stap | Wat | Soort | Grootte | Klaar als |
|---|---|---|---|---|
| 2.1 | G2: velden op de ploegpagina | UI | S | leeftijd, plaats en tekst staan bovenaan |
| 2.2 | G1: blok "Welke groep?" | UI, misschien code | M | een ouder vindt de groep in twee klikken |
| 2.3 | G3, G4: knop op gsm en grotere tikdoelen | code | S | PageSpeed keurt de tikdoelen goed |
| 2.4 | T1, T2: contrast | code | S | alle witte tekst 4,5 op 1 of hoger |
| 2.5 | G7: kalender na een klik | code | S | Rondes scoort 100 op praktische tips |
| 2.6 | G5, G9, I1: teksten en privacy | leiding | M | lijst afgevinkt door de hoofdleiding |
| 2.7 | G6: Nieuws in de voettekst | UI | S | link staat er |

### Fase 3 — Op de dag van de livegang

1. De lijst onder "H" afwerken op de echte server.
2. L5: browsercache aanzetten.
3. Sitemap opnieuw maken met het echte domein.
4. Dezelfde crawl draaien op het echte domein.
5. Search Console: domein bevestigen en sitemap indienen.

### Fase 4 — Na de livegang

- PageSpeed opnieuw, nu op het echte domein.
- Na één week, één maand en drie maanden: indexering en 404's in Search Console.
- S10: de links op `deinze.be`, `jeugdraaddeinze.be` en KSA Nationaal nakijken.

### Later, zonder haast

L4 (vos apart laden), L6 (HTML-fouten), S6 (gestructureerde gegevens), S8 (favicon), G8 (kleine dingen), I2.

## Keuzes voor jou

1. **Leiderpagina's.** Mijn voorstel: `noindex` en uit de sitemap. Alternatief: doorsturen naar de ploeg, of laten staan en wel linken.
2. **Contrast.** Mag de donkere laag in de voettekst hoger dan de 20% uit Figma?
3. **Google-kalender.** Mijn voorstel: laden na een klik. Alternatief: weglaten, of laten zoals het is.
4. **"Welke groep?"** Een nieuw blok op de startpagina staat niet in Figma. Akkoord?
5. **Bezoekcijfers.** Er is nu geen meting. Geen, een privacyvriendelijke teller zonder cookies, of Google Analytics met een cookiebanner?
6. **Productie.** Welke omgeving wordt het? Die vraag staat open sinds 5 november.

## Grenzen en onzekerheid

- Geen test op een echte telefoon en geen schermlezer.
- PageSpeed is een labmeting. Er zijn geen cijfers van echte bezoekers.
- De testsite is niet de productieserver. Snelheid en headers kunnen daar verschillen.
- W3C valideerde 5 van de 10 pagina's. Daarna blokkeerde de dienst mijn aanvragen (fout 429).
- Contrast op de foto is nagerekend, niet met een officieel hulpmiddel gemeten.
- Geen toegang tot Search Console of een Google Bedrijfsprofiel.
- De Google-resultaten komen uit jouw ingelogde browser en zijn dus persoonlijk gekleurd.
- De test "eerste bezoeker" las alleen tekst. Elke bevinding is daarna nagekeken op de site. De rest is weggelaten.
- Formulieren, mails en het inloggen als leiding zijn niet getest. Dat deed de scan van 5 oktober.
