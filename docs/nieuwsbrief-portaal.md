# Nieuwsbriefportaal

## Gebruik

- Insturen: `/nieuwsbrief/insturen`. Leiding en administrator hebben rechtstreeks toegang. Anderen gebruiken de gedeelde instuurlink.
- Beheer: `/admin/content/nieuwsbrief`, uitsluitend voor de administrator. Filter op titel/schrijver en instuurperiode.
- Instuurlink beheren: `/admin/content/nieuwsbrief/instuurlink`. Vernieuwen trekt ook bestaande instuursessies in.
- De lokale app voor het samenstellen/exporteren van een nieuwsbrief is een volgende stap.

## Gegevens

Eigen interne entity `newsletter_article`: titel, schrijver, tekst, optionele activiteitsdatum, automatische instuurdatum, ingelogd account indien aanwezig en maximaal vijf Media-foto’s. Geen editie of workflowstatus. De instuurperiode bepaalt de editie later.

Tekst bewaart een vaste veilige HTML-subset: `p`, `br`, `strong`, `em`, zonder attributen. Drupal's meegeleverde CKEditor biedt alleen vet/cursief. Geplakte opmaak wordt beperkt. Servervalidatie normaliseert ook handmatig vervalste verzoeken. Zonder JavaScript blijft een gewoon tekstvak bruikbaar. De toekomstige lokale app moet deze HTML-subset behouden of naar eigen opmaak omzetten.

## Bescherming

- Willekeurige herroepbare instuurlink, sessiegebonden CSRF-token en Honeypot met wachttijd.
- Maximaal 10 pogingen per uur/IP, 25 per dag/IP en 100 per dag totaal; ook foutieve inzendingen tellen mee. IP-identificatie via HMAC.
- Maximaal 20.000 teksttekens. Maximaal vijf JPG/PNG/WebP-bestanden, 5 MB elk, 20 MB samen, 25 megapixels en 10.000 pixels per zijde. Inhoud wordt als echte afbeelding gevalideerd.
- Foto’s als ongepubliceerde Media, met originals en afgeleiden in private opslag. Expliciete toegangscontrole blijft ook gelden als Media per ongeluk gepubliceerd wordt.
- Bijdragen zijn intern, niet automatisch beschikbaar via JSON:API en nooit publiek op de site. Formulier en beheerpagina’s hebben no-store/noindex en no-referrer.
- Herhaalde POST met dezelfde formulier-UUID maakt geen dubbele bijdrage.

## Installatie en onderhoud

1. Installeer dependencies uit Composer lock (bevat Drupal 11.4.10, Gin 5.0.16 en Honeypot 2.2.2).
2. Maak een schrijfbare private map buiten `web` en configureer `$settings['file_private_path']` in de lokale serverinstellingen. Neem deze map voortaan mee in backups, samen met database en publieke uploads.
3. Schakel KSA Nieuwsbrief en Honeypot in via Uitbreiding in de browser. De module installeert uitsluitend haar eigen entiteit en private Media-type.
4. Genereer de instuurlink via de beheerpagina. Geen tokens in Git of documentatie bewaren.
5. Controleer `scripts/check-newsletter.php`, `scripts/check-newsletter-validation.php`, `scripts/check-editor-access.php` en `scripts/check-leadership-log.php`.

Lokaal gecontroleerd: anoniem insturen met tekst/foto, vet+cursief bewaren en weergeven, geweigerde private download, ingetrokken link, limieten, CSRF en schadelijke HTML. Desktop en mobiel volgen de bestaande pagina-intro, Inter, groene actieknop, witruimte en subtiele rand.

## Testomgeving 19 oktober 2026

Uitgerold op `https://ksadeinzetest.alwaysdata.net` na database- en hostinginstellingenbackup buiten de webmap. Modules via de browser geïnstalleerd, instuurlink via het beheerformulier aangemaakt. Drupal 11.4.10 en Gin 5.0.16; geen database-updates nodig en Composer audit zonder meldingen. Bestaande hostingregels en testsite-noindex behouden.

Online: 416 rechtencontroles, 31 nieuwsbriefbeveiligingscontroles (vóór foto-test), 13 invoer-/uploadcontroles en 35 logcontroles geslaagd. Fictieve browserbijdrage `TEST — opmaak nieuwsbrief` met vet/cursief en één kleine testafbeelding opgeslagen en teruggelezen. Deze testbijdrage staat uitsluitend in het private nieuwsbriefbeheer.
