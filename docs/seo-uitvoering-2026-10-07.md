# SEO en tekstredactie — 7 oktober 2026

Lokaal uitgevoerd op `ksa.ddev.site`. Niet gedeployed. De teksten richten zich op ouders die een jeugdbeweging in Deinze/Astene zoeken, bestaande leden die praktische informatie nodig hebben en jeugdgroepen die een lokaal willen huren. Zoekwoorden zijn natuurlijk verwerkt; namen, contactgegevens, capaciteit en prijzen zijn niet verzonnen.

## Voor en na

| Onderdeel | Voor | Na | Waarom |
|---|---|---|---|
| Homepage | “Samen spelen, avonturen beleven en vrienden maken.” | “De jeugdbeweging voor kinderen en jongeren in Deinze en Astene.” Met welkom vanaf het eerste leerjaar. | Meteen duidelijk wat, waar en voor wie. |
| Leiding | “Maak kennis met de leiding” en algemene introductie. | “Leiding en leeftijdsgroepen”, met uitleg welke groep bij je kind past. | Antwoord op de eerste vraag van ouders. |
| Groepsteksten | Algemene, wisselende beschrijvingen. | Zeven openbare groepen beschrijven concreet leerjaren, plaats en leiding. | Sneller de juiste groep vinden. |
| Verborgen groep | +16 was eerder verborgen. | Blijft ongepubliceerd; ook uitgesloten uit sitemap en niet toegankelijk voor bezoekers. | Geen aanbod tonen dat dit jaar niet bestaat. |
| Rondes | “Activiteiten & Kampen” | “Rondes, activiteiten en kampen”, met uitleg over het rondeboekje. | Vertrouwde KSA-term én begrijpelijke woorden voor nieuwe ouders. |
| Gedeeld rondeblok | “Wekelijkse rondes”; algemene tekst en “Bekijk de data”. | “Rondes: onze wekelijkse activiteiten”; download je boekje voor uren, afspreekplaats en benodigdheden. | Duidelijke vervolgstap. |
| Inschrijving | “Hoe schrijf ik in?” | “Inschrijven en praktische info”; korte introductie over Ravot, lidgeld, verzekering, uniform en locaties. | Meer informatie in een scanbare opening. |
| Ravot-stappen | “Surf naar ‘ravot.ksa.be’”; “Maak een account aan voor je kind”. | “Open Ravot”; “Voeg je kind toe”, met concrete handelingen. | Minder verwarring tussen ouderaccount en lid toevoegen. |
| Verzekering | Algemene belofte over drie gratis verzekerde kennismakingsrondes. | Vooraf afspreken met de leiding; link naar de officiële voorwaarden van KSA. | Geen onvoorwaardelijke verzekeringsbelofte. |
| Ziekenfonds | “Mutualiteit” | “Terugbetaling via je ziekenfonds”, met voorwaarden navragen en attest via hoofdleiding. | Begrijpelijke taal en concrete actie. |
| Uniform | Algemene uniforminformatie. | “Uniform en speelkleren”; duidelijk wat niet verplicht is en waar je bestelt. | Praktische verwachtingen helder. |
| Uren en locaties | Verspreide praktische uitleg. | “Wanneer en waar spreken we af?” met gebruikelijke uren, adressen en boekje als actuele bron. | Minder zoekwerk en ruimte voor afwijkende uitstappen. |
| Ons verhaal | “Ons verhaal”, zonder intro en met abstracte formuleringen. | “Over onze jeugdbeweging”; samen spelen, rondes, kampen, kennismaken en geschiedenis sinds 1932. | Concreet beeld van de werking; bruikbare metabeschrijving. |
| Verhuur | “Welkom bij ‘t Brielhof!” en lange introductie over nieuwbouw. | “Jeugdlokaal ’t Brielhof huren in Deinze”; locatie, doelgroep en boeken via Kampas. | Sluit aan op de bedoeling van bezoekers die een lokaal zoeken. |
| Route en boeken | Lange routebeschrijving; algemene boekingsknop. | Route in drie stappen; “Bekijk beschikbaarheid op Kampas”; verhuurbeperkingen helder. | Sneller beslissen en navigeren. |
| Contact | “Vragen? Contacteer ons!” | “Contact met KSA Deinze-Astene”; uitleg wie helpt bij welke vraag. | Gerichter contact zoeken. |
| Nieuwsarchief | Drie gemigreerde berichten; geen afzonderlijke archiefpagina. | `/nieuws`, tien berichten verdeeld over twee pagina’s; zeven ontbrekende berichten uit het zichtbare oude overzicht toegevoegd. | Oude inhoud blijft vindbaar zonder voorbije acties als actueel te presenteren. |
| Archiefteksten | Ontbrekende oudere berichten. | Korte teksten in verleden tijd, met oorspronkelijke publicatiedatum en jaartal. | Duidelijk onderscheid tussen geschiedenis en huidige agenda. |
| SEO-titels | Voornamelijk generieke automatische titels. | Gerichte homepage-, contact- en verhuurtitels; nieuws met paginanummer. | Duidelijkere zoekresultaten en onderscheid tussen pagina’s. |
| Metadata beheer | Geen metatagveld op basispagina’s. | Automatische defaults met admin-overrides; verborgen en niet bewerkbaar voor leiding. | Flexibele SEO zonder extra beheerdruk. |
| Gestructureerde gegevens | Geen organisatie-/website-/artikelmarkup. | Organization, WebSite en BlogPosting via Schema Metatag. | Machineleesbare context over organisatie en artikelen. |
| Sitemap | Geen ingestelde XML-sitemap. | `/sitemap.xml` met 58 openbare URL’s; geen foutpagina of verborgen +16. | Overzicht van indexeerbare inhoud. |
| Oude adressen | Oude pagina-, blog- en PDF-URL’s niet afgehandeld. | 24 handmatige 301-redirects, waaronder zeven PDF’s; ook `/home` en `/node/39` naar `/`. | Bestaande links blijven bruikbaar bij migratie. |
| Nieuwspaginering | Geen afzonderlijk bruikbaar archief met correcte SEO-paginering. | Eigen canonical per geldige pagina; ongeldige paginanummers geven echte 404 + noindex. | Geen lege of dubbel geïnterpreteerde archiefpagina’s. |
| Foutpagina | Directe foutpagina kon als gewone inhoud worden geïndexeerd. | Noindex en uitgesloten uit sitemap. | Geen foutpagina in zoekresultaten. |

De namen, telefoonnummers en groepskoppelingen van de 31 leiders zijn niet redactioneel verzonnen. Bestaande duidelijke teksten zijn behouden. De drie eerder overgezette nieuwsberichten zijn gecontroleerd en behouden. Zeven extra archiefberichten: Quiz 2026, Kerstrozen 2025, Cavabar 2025, Startdag 2025, Ontbijtmanden voor Moederdag 2025, Amerikaanse avond van de Sjo 2025 en Quiz 2025. Dit is geen claim dat het volledige historische archief van de oude site is overgezet.

## Controle

- `ddev drush php:script scripts/check-seo.php`: **222 controles, 0 fouten**. Anonieme rendering per aparte Drupal-kernel; één H1, beschrijving en canonical per openbare pagina, redirects, sitemap, paginering, statuscodes en noindex gecontroleerd. Rapport: `qa/seo-after-2026-10-07.json`.
- `ddev drush php:script scripts/check-editor-access.php`: **402/402** controles geslaagd. Geen gebruikers of inhoud opgeslagen door de test. Inclusief blokkering en verbergen van SEO-velden voor leiding.
- PHP-syntaxcontrole nieuwe SEO-module geslaagd; `git diff --check` schoon.
- Nieuws visueel bekeken op 390px en 1440px, zonder horizontale overflow; inschrijfpagina op standaard browserbreedte bekeken. Screenshots: `qa/seo-nieuws-after.png` en `qa/seo-inschrijven-after.png`.
- Deze controle vervangt geen echte gebruikerssessie voor een afzonderlijke leidingaccount, geen productie-HTTP-test en geen Google Search Console-controle.

## Implementatie en herstel

Contrib: Redirect 1.13.0, Simple XML Sitemap 4.2.3, Schema Metatag 3.1.0 en Metatag Views. Kleine module `ksa_seo` vult het ontbreken van strikte Views-paginavalidatie en querybewuste canonicals aan. Content en configuratie zijn via Drupal UI aangepast.

UI-configuratie-export van 7 oktober 09:29 UTC selectief in `config/sync/` verwerkt: nieuwe SEO-configuratie, metatagveld, paginaweergaven, modulelijst en nieuws-View. Onverwante beeldstijlwijziging en automatische vertaalverschillen in andere Views niet gekopieerd. Geen automatische config-import uitgevoerd.

Lokale databasebackups vóór en na: `.ddev/seo-before-20261007.sql.gz` en `.ddev/seo-after-20261007.sql.gz` (buiten Git). Teksten, Layout Builder-blokken, redirects en individuele sitemap-uitsluitingen zijn database-inhoud: config alleen volstaat niet om deze wijzigingen naar een andere omgeving te brengen. Behoud ook `web/sites/default/files`.

## Nog te bevestigen of bij livegang

- **Lidgeld:** lokale pagina vermeldt €37; oude publieke site €35. Het huidige bedrag van €37 is behouden totdat Arne het bedrag voor dit werkjaar bevestigt.
- Kennismaking wordt niet als automatisch gratis verzekerd omschreven. Actuele regeling door leiding laten bevestigen; officiële KSA-voorwaarden blijven leidend.
- Pas bij livegang: domein/HTTPS/robots/canonicals op het echte domein controleren, sitemap opnieuw genereren en indienen in Search Console, productieprestaties meten en indexatie opvolgen. Er is niets gedeployed of bij Google ingediend.
- Een volledige historische URL-lijst uit Search Console/serverlogs kan nog oudere links opleveren buiten de tien berichten op het bekeken oude nieuwsoverzicht.

## Bronnen

- [Oude werking](https://ksadeinze.be/werking), [nieuwsoverzicht](https://ksadeinze.be/blog), [rondes](https://ksadeinze.be/rondes), [inschrijven](https://ksadeinze.be/inschrijven) en [lokalen](https://ksadeinze.be/lokalen): bestaande feiten en migratielinks.
- [Officiële KSA-verzekeringsinformatie](https://www.ksa.be/verzekering): voorwaarden voor leden en kennismaking; geen algemene belofte van drie rondes.
- [Google over titellinks](https://developers.google.com/search/docs/appearance/title-link): beschrijvende paginatitels.
- [Redirect](https://www.drupal.org/project/redirect), [Simple XML Sitemap](https://www.drupal.org/project/simple_sitemap), [Schema Metatag](https://www.drupal.org/project/schema_metatag): gebruikte modules.
