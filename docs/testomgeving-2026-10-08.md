# Testomgeving bijgewerkt — 8 oktober 2026

Testsite: https://ksadeinzetest.alwaysdata.net/

## Overdracht

- Code via Git: `819a4e1` (ontwerp, SEO, leidingcode), `5668f59` (Single Content Sync), `06ea661` (uitlijning).
- SEO-instellingen via afzonderlijke Drupal-configuratie-importformulieren: homepage, 404 en artikelmetatags, metatagveld op basispagina’s met formulier/weergave, nieuws-View, sitemapbundels en custom links.
- SEO-modules en Single Content Sync via de modulebeheerpagina ingeschakeld. Geen gebruikersdatabase vervangen en geen online wachtwoorden gewijzigd.
- Single Content Sync 1.4.18 via de browser: 58 nodes met media en inline Layout Builder-blokken geëxporteerd en geïmporteerd. Het archief bevat geen user-entiteiten of wachtwoordvelden.
- 24 redirects via dezelfde browserexport/import overgezet.
- Herbruikbaar blok 34 wordt niet met de inline pagina-indelingen bijgewerkt: de gedeelde tekst “Wekelijkse rondes” daarom apart via het online blokformulier bijgewerkt.
- Foutpagina node 51 via het formulier uitgesloten uit de sitemap, waarna de sitemap via diezelfde UI opnieuw is gegenereerd.
- Geen volledige configuratie-import of database-import uitgevoerd. De bestaande online mail-, account- en hostinginstellingen blijven behouden. De leidingrechten zijn tijdens deze specifiek op SEO en inhoud gerichte overdracht niet geïmporteerd.
- De geplande footerwijziging is nog niet uitgevoerd.

## Controle

- Online inhoud van Home, Leiding, Rondes, Ons verhaal, Verhuur en Contact vergeleken met lokaal: gelijk.
- Inschrijving: nieuwe Ravot-stappen, verzekeringsinformatie en praktische tabbladen aanwezig; mobiele stapweergave gecontroleerd.
- 10 artikelen, 31 leiders, 8 basispagina’s en 9 groepen opgeslagen; +16 ongepubliceerd en anoniem niet toegankelijk (403).
- Publieke hoofdpagina’s en nieuwsarchief geven 200. Canonicals verwijzen naar het testdomein, inclusief `?page=1` voor de tweede nieuwspagina. Ongeldige nieuwspaginering geeft 404.
- `/inschrijven` leidt naar `/inschrijving`.
- Sitemap geeft 200 en bevat 58 URL’s op het testdomein, zonder lokale ddev-links of node 51.
- `X-Robots-Tag: noindex, nofollow, noarchive` en de bestaande test-robotsregels behouden. Deze testsite hoort niet door Google geïndexeerd te worden.
- Uitlijning lokaal op desktop gemeten: nieuwsintro’s en leeslinks staan op gelijke y-posities; groepsfoto’s gebruiken `contain` met linkeruitlijning, zonder personen af te snijden. Online CSS en geladen groepsfoto’s bevestigd.

Bewijs: `qa/testsite-online-2026-10-08.png`.

## Bij een volgende overdracht

Gebruik **Instellingen → Content authoring → Single Content Sync → Exporteren** en op de testsite **Inhoud → Importeren**. Exporteer alleen de gewenste inhoudstypes; import werkt op UUID en werkt bestaande inhoud bij. Houd herbruikbare blokken, redirects en individuele sitemap-uitsluitingen apart in het oog. Instellingen gaan via **Configuratie synchroniseren → Importeren → Single item**. Alle inhoudsarchieven blijven buiten Git.
