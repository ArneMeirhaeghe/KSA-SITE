# Nieuwsbrief-API (versie 1)

## Toegang

Admin: `/admin/content/nieuwsbrief/api`. Maak daar een aparte alleen-lezen API-sleutel aan en kopieer deze direct naar de lokale app. Drupal toont de sleutel één keer en bewaart uitsluitend de SHA-256-hash. Vervangen of intrekken maakt de bestaande sleutel meteen ongeldig. Leiding en de instuurlink krijgen geen API-leesrecht. Ook een ingelogde admin moet voor API-aanvragen de Bearer-sleutel meesturen.

Gebruik uitsluitend HTTPS. Bewaar de sleutel in de veilige opslag van de lokale app of een omgevingsvariabele, niet in broncode, URL's of logbestanden. Dit is een server-/desktopapp-koppeling; er worden geen brede CORS-rechten aan websites toegekend.

## Artikelen ophalen

`GET https://ksadeinzetest.alwaysdata.net/api/nieuwsbrief/artikelen?van=2026-10-01&tot=2026-10-31&limit=50`

Header: `Authorization: Bearer <API-sleutel>`

- `van` en `tot`: optioneel, geldige YYYY-MM-DD, filter op instuurdatum in Europe/Brussels. Beide dagen inclusief.
- `limit`: 1–100, standaard 50.
- `after`: oplopende interne cursor; volg bij voorkeur de meegeleverde `pagination.next`-URL. Geen dubbels door nieuwe inzendingen tijdens het pagineren.
- Eén artikel heeft een stabiele UUID. Titels wijzigen heeft geen invloed op deze identiteit.

Voorbeeldschema (fictieve IDs en voorbeeldgegevens):

```json
{
  "schema_version": 1,
  "data": [{
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "title": "Een avontuur met onze groep",
    "writer": "Voorbeeld",
    "text_html": "<h2>Ons verhaal</h2><p>Dit was <strong>leuk</strong>.</p>",
    "submitted_at": "2026-10-19T18:00:00Z",
    "activity_date": null,
    "photos": [{
      "id": "550e8400-e29b-41d4-a716-446655440001",
      "filename": "groepsfoto.jpg",
      "mime_type": "image/jpeg",
      "size_bytes": 123456,
      "width": 1600,
      "height": 1200,
      "url": "https://ksadeinzetest.alwaysdata.net/api/nieuwsbrief/artikelen/550e8400-e29b-41d4-a716-446655440000/fotos/550e8400-e29b-41d4-a716-446655440001",
      "download_url": "https://ksadeinzetest.alwaysdata.net/api/nieuwsbrief/artikelen/550e8400-e29b-41d4-a716-446655440000/fotos/550e8400-e29b-41d4-a716-446655440001",
      "requires_authorization": true,
      "public_url": null,
      "public_image": null
    }]
  }],
  "pagination": {"count": 1, "next": null}
}
```

`text_html` wordt door het opgeslagen Drupal-tekstformaat gefilterd, net zoals de artikelweergave. Het standaardformaat Nieuwsbrief bevat beperkte HTML-opmaak. Een artikel zonder foto's heeft `photos: []`. `submitted_at` staat in UTC; activiteitsdatum is een lokale kalenderdatum of null.

## Foto's

Download de bytes via `photos[].download_url`, met dezelfde Authorization-header. De URL is op artikel- en Media-UUID gebaseerd, niet op bestandsnaam. De app kan lokaal opslaan als `<foto-id>.<extensie>`, zodat gelijknamige uploads nooit botsen.

De foto-route geeft alleen foto's die bij het genoemde nieuwsbriefartikel horen. Privébestanden van andere onderdelen van de site zijn niet bereikbaar. Bestanden blijven afgeschermd; alleen het bezit van de URL geeft geen toegang.

**Gebruik voor e-mail `photos[].public_url`.** Die is `null` zolang een admin de foto niet expliciet heeft gepubliceerd. De beveiligde `download_url` blijft bedoeld voor de lokale app; e-mailprogramma's sturen geen API-header mee.

Open een artikel in `/admin/content/nieuwsbrief`, kies **Foto’s selecteren en publiceren**, vink de gewenste foto's aan en bevestig dat deze openbaar gedeeld mogen worden. Drupal maakt afzonderlijke kopieën van standaard maximaal 1600 × 1600 pixels (aanpasbaar bij Structuur → Nieuwsbrief), zonder bijsnijden of vergroten. JPEG blijft JPEG; PNG en WebP worden PNG. De privé-originelen blijven ongewijzigd en beveiligd.

Na publicatie bevat `public_url` de absolute openbare afbeeldingslink. `public_image` bevat `id` (UUID van de kopie), `url`, `width`, `height` en `mime_type`. De bestandsnaam is een willekeurige UUID, onafhankelijk van de titel en originele bestandsnaam. Iedereen met de openbare link kan deze kopie bekijken.

Opnieuw publiceren gebruikt standaard dezelfde kopie. Met **Nieuwe versie maken** krijgt de foto een nieuwe UUID en URL; de vorige kopie blijft bestaan zodat verstuurde nieuwsbrieven blijven werken. Verwijder die oude Media/bestanden dus niet zolang hun links nodig zijn. Gebruik voor echte verzending het definitieve domein en behoud dat domein plus bestanden bij een verhuizing; testomgevingslinks zijn alleen voor tests.

## Fouten en limieten

- 400: HTTP of ongeldige filters/paginering.
- 401: ontbrekende, verkeerde of ingetrokken sleutel.
- 404: onbekende artikel-/fotocombinatie of ontbrekend bestand.
- 405: schrijfmethode niet toegestaan (GET/HEAD alleen).
- 429: standaard maximaal 120 API-aanvragen per minuut per IP (instelbaar bij Structuur → Nieuwsbrief), inclusief downloads; respecteer `Retry-After`.

Alle API-antwoorden zijn private/no-store/noindex. Geen sleutels in paginalinks. Geen wijzigingen, verwijderingen of publicaties via dit endpoint.

Controle: `ddev drush php:script scripts/check-newsletter-api.php`. Deze gebruikt een tijdelijke testsleutel en transactie, en herstelt de eerdere configuratie. Voer niet gelijktijdig met sleutelbeheer uit.

Publicatiecontrole: `ddev drush php:script scripts/check-newsletter-public.php`; maakt uitsluitend tijdelijke synthetische afbeeldingen en ruimt deze weer op.
