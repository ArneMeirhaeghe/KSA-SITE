# Rondes-pagina (Activiteiten & Kampen)

Figma: bouwplan 107:2680 (desktop), 129:2735 (mobiel). Node 34, pad `/rondes`.

## Opbouw (Layout Builder, per-pagina-override)

| Sectie | Lay-out | Inhoud |
|---|---|---|
| 0 | 1 kolom | Intro-veld (+ Body, leeg) |
| 1 Wekelijkse rondes | 1 kolom | **Gedeeld blok** "Wekelijkse rondes (Rondes en startpagina)" + views-blok **Rondeboekjes** |
| 2 Weekends en kampen intro | 1 kolom | Basic-blok (h2 + tekst), gecentreerd |
| 3 Weekends en kampen | 3 kolommen 33/34/33 | 3 Infokaarten zonder foto |
| 4 Data en planning | 1 kolom | Blok **Agenda uit Google** (`ksa_agenda`) |

## Gedeeld met de startpagina (sinds 5 oktober 2026)

- De sectie "Wekelijkse rondes" staat op Rondes **én** op de startpagina, onder het nieuws.
- Tekst = herbruikbaar blok **"Wekelijkse rondes (Rondes en startpagina)"**.
  Aanpassen via **Inhoud → Blokken** → Bewerken. Beide pagina's veranderen mee (getest op 05-10).
- Rondeboekjes = dezelfde view `booklets`. Een PDF wijzigen op een Ploeg wijzigt beide pagina's.
- Niet in Layout Builder aanpassen per pagina, anders lopen ze uit elkaar.
- Laatste sectie op de startpagina: CSS geeft 135 px ruimte boven de footer (`layout.css`).
- Backup van het oude losse blok: `backups/2026-10-05-voor-gedeeld-rondesblok/`.

## Rondeboekjes

- Veld **Rondeboekje** (`field_booklet`) op Ploeg: Media, type Document (PDF), 1 waarde, optioneel.
- Nieuw boekje? Ploeg bewerken → Rondeboekje → oud verwijderen → nieuwe PDF opladen → Opslaan.
- Geen boekje = ploeg staat niet op de pagina (bv. +16).
- View `booklets` (Rondeboekjes): gepubliceerde ploegen met een boekje, sortering Volgorde en Titel, weergave **Download**.
- Weergave Download (node.team.download): Logo (Contactlogo 480) + Rondeboekje (label, geen link).
- Template `templates/content/node--team--download.html.twig` → component `download-card`.
- De kaart opent de PDF in een **nieuw tabblad** (`target="_blank" rel="noopener"`); schermlezers horen "Rondeboekje Dolfijntjes, PDF, opent in een nieuw tabblad".
- Media 37–43 (7 PDF's).

## Weekends en kampen

- Infokaart: veld Foto is nu **optioneel**. Zonder foto = tekstkaart met rand (`info-card--text`).
- Tekstveld: één regel per item. "Weekend: twee dagen" → "Weekend:" wordt vet (label vóór de dubbele punt, max. 20 tekens).
- Sinds 5 oktober 2026: per groep de **data** van weekend en kamp, werkjaar 2026–2027, overgenomen uit de Google Agenda.
  Backup van de oude tekst (enkel de duur): `backups/2026-10-05-voor-kampdata/infokaarten-rondes.txt`.
- ⚠️ Dit is vaste tekst. Nieuw werkjaar = data wijzigen in Google **én** in deze 3 kaarten (Layout Builder, sectie 3).

## Agenda (Google)

- Sectie 4 bevat het blok **Agenda uit Google** (module `ksa_agenda`), sinds 5 oktober 2026.
- Het oude, tijdelijke blok met vaste data is weg. Inhoud bewaard in `backups/2026-10-05-voor-agenda-module/oud-agenda-blok.html`.
- Hoe het werkt en hoe de leiding het beheert: zie `docs/agenda.md`.

## CSS

- `components/download-card/*`, `components/info-card/info-card.css` (tekstkaart), `css/layout.css` (blok "Rondes page").
- Backup: `backups/2026-09-30-voor-rondes/`.
