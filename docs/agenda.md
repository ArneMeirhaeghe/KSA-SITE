# Agenda op de Rondes-pagina

Status: gebouwd op 5 oktober 2026. Sinds 5 oktober 2026 gekoppeld aan **"KSA Kalender"**
(`c_657ec4dc1662b0a083c1634b28730393f2c39b0838186a1ab01909b170009c91@group.calendar.google.com`).
Figma: sectie "Data en Planning" in bouwplan 107:2680.

## Wat de bezoeker ziet

- Links: kaarten "Aankomende evenementen", 5 per pagina.
- Paginering zoals in Figma (gsm-nieuwslijst, frame 121:1947): "‹ Vorige 1 2 3 Volgende ›".
  Maximaal 3 nummers, rond de huidige pagina. Huidige pagina = groen vak met donkere tekst.
  Onder 360 px breed: enkel pijltjes (44 × 44), de woorden blijven voor schermlezers.
- Rechts: de maandkalender van Google. Op gsm staat die onder de kaarten.
- Rechtsboven: **Abonneren**, met twee keuzes. Beide openen in een nieuw tabblad.
  - Google Agenda.
  - iPhone, Mac of Outlook (webcal-link).
- Een kaart aanklikken = enkel die activiteit in je eigen agenda zetten (`.ics`-bestand).
- De paginering springt terug naar de agenda (`#agenda`), niet naar boven.

## Hoe de leiding het beheert

1. Activiteiten zet je **alleen in Google Agenda**. Niet in Drupal.
2. De agenda moet **openbaar** zijn in Google.
3. Welke agenda de site toont: **Beheer → Configuration → Web services → Agenda**
   (`/admin/config/services/ksa-agenda`). Plak de ID, de link of de insluitcode.
4. Na opslaan haalt de site de agenda meteen op, en meldt hoeveel activiteiten.
5. Daarna haalt cron ze opnieuw op, ongeveer om de 3 uur.

Een **geheime** iCal-link weigert het formulier. Die werkt als een wachtwoord.

Uitleg voor de leiding (sinds 5 oktober 2026), als groene melding "Uitleg":
- **Agenda-formulier:** activiteiten beheer je in Google Agenda, niet hier. Met link "Open Google Agenda".
- **Blok "Agenda uit Google" in Layout Builder:** zelfde boodschap, met link naar het agenda-formulier.
- Gebouwd als echte Drupal-statusmelding: contrast 4,54 : 1 (een gewone tekst in die kleur haalde maar 1,23 : 1).

## Hoe het technisch werkt

| Onderdeel | Wat het doet |
|---|---|
| `CalendarId` | Zet geplakte tekst om naar één agenda-ID. Bouwt de links. |
| `EventParser` | Leest de feed met `sabre/vobject`. Rekent herhalingen uit (RRULE, EXDATE). |
| `DateLabel` | Schrijft de datum in het Nederlands, bv. "29 – 31 januari 2027". |
| `AgendaFetcher` | Haalt de feed op in cron en bij opslaan. Bewaart in State. |
| `EventIcs` + `EventController` | Eén activiteit als `.ics` op `/agenda/activiteit/{id}`. |
| `AgendaBlock` | Blok "Agenda uit Google" voor Layout Builder. Maakt ook de paginalinks (met `#agenda`). |
| Thema: `ksa:agenda` | Opbouw van de sectie. Kaarten zijn `ksa:card`, variant `event`. |

- Ophalen gebeurt **nooit** bij een paginabezoek. Google traag? De pagina niet.
- Feed stuk? De laatste goede versie blijft staan (State overleeft `drush cr`).
- Na elk ophalen wordt cache-tag `ksa_agenda` leeg gemaakt. Zo verdwijnen ook voorbije activiteiten.
- Een kamp dat nu bezig is, blijft staan tot de laatste dag.

## Controles (nog geen PHPUnit)

```bash
ddev exec php scripts/check-calendar-id.php
ddev exec php scripts/check-event-parser.php
ddev exec php scripts/check-date-label.php
ddev exec php scripts/check-event-ics.php
```

Samen 48 controles. Dit zijn losse scripts, geen PHPUnit-tests.

## Vertaling

De agenda heeft een eigen paginering in de component `ksa:agenda`, met Nederlandse tekst.
De standaard Drupal-paginering (Nieuws, views) was Engels. Die vertaling staat in
`translations/ksa-pager.nl.po`, geïmporteerd via Gebruikersinterface vertalen.

## Nog open

- De rol `content_editor` heeft nog geen recht "Agenda-instellingen beheren".
- Groep op de kaart (Figma: "Lokaal - (Dolfijntjes)") is weggelaten. De titel noemt de groep.
