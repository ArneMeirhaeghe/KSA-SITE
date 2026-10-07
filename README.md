# KSA Deinze-Astene — Drupal

Drupal 11 met het KSA-thema, Single Directory Components, Views, Media en Layout Builder. De lokale site draait op https://ksa.ddev.site.

## Lokaal werken

1. Installeer DDEV en start Docker.
2. Voer `ddev start` en `ddev composer install` uit.
3. Herstel de laatste database- en bestandenbackup. Configuratie alleen bevat geen inhoud, media of pagina-layouts die als inhoud zijn opgeslagen.
4. Gebruik `ddev drush uli` voor de beheerlogin. Voer PHP en Composer altijd via DDEV uit.

Inhoud, media, menu's, velden, Views, rechten en Layout Builder worden via de Drupal-beheerinterface gewijzigd. Codewijzigingen beperken zich tot het thema, de agendamodule, de leidinginterface, een kleine SEO-koppeling voor nieuwspaginering en controlescripts. De rol **Leiding** start op `/admin/leiding` en beheert teksten, nieuws, bestaande ploegen, leiders, foto's en PDF's. Paginaopbouw, agenda-instellingen en gebruikersbeheer blijven bij de beheerder. Zie `docs/leidingbeheer.md` voor gebruik en technische grenzen.

## Configuratie en herstel

`config/sync/` bevat de volledige export van 6 oktober 2026, met de gecontroleerde wijzigingen voor leidingbeheer uit de UI-export van 7 oktober. Ook de SEO-configuratie uit de UI-export van 7 oktober is selectief overgenomen (Metatag, sitemap, Redirect en nieuwsarchief); een andere actieve wijziging aan `media.image.article_wide` is niet meegenomen. Exporteer later opnieuw via **Instellingen → Ontwikkeling → Configuratie synchroniseren → Exporteren**, controleer de verschillen en werk deze map bewust bij. Gebruik geen automatische import of export bij opstarten.

Voor een nieuwe omgeving met dezelfde site-UUID kan de beheerder het archief via de importinterface aanbieden en de verschillen beoordelen. Voor een volledige demo zijn ook een databasebackup en `web/sites/default/files` nodig. Backups en lokale instellingen blijven buiten Git. Bewaar backups op een veilige plek; publiceer ze niet.

## Thema en inhoud

- `web/themes/custom/ksa/components/`: SDC-componenten met Twig, CSS en waar nodig Drupal behaviors.
- `web/themes/custom/ksa/templates/`: dunne Drupal-koppelingen naar componenten; veldformatters blijven in Drupal configureerbaar.
- `web/themes/custom/ksa/css/tokens.css`: bestaande ontwerpwaarden uit Figma.
- `web/modules/custom/ksa_agenda/`: openbare Google Agenda ophalen, herhalingen verwerken, activiteiten en ICS tonen.
- `web/modules/custom/ksa_seo/`: correcte canonicals en 404-status voor nieuwspaginering; overige SEO via contrib-configuratie. Zie `docs/seo-uitvoering-2026-10-07.md`.
- `translations/`: aanvullende Nederlandse interfacevertalingen, importeren via de beheerinterface.

Layout Builder Styles biedt vier benoemde sectiestijlen: **Inschrijvingsstappen**, **Praktische tabbladen**, **Rondeboekjes in kader** en **Tekst naast foto**. Stappen en tabbladen werken alleen binnen de bijbehorende sectie. Hun blokvolgorde bepaalt de volgorde in de navigatie. In de layout-editor en zonder JavaScript blijft alle inhoud leesbaar.

De agenda bewaart de laatste geslaagde feed. Cron haalt wijzigingen op; de beheerpagina toont de laatste succesvolle synchronisatie. Een middleware vóór Internal Page Cache ververst datumafhankelijke agendaweergaven bij de eerste aanvraag van een nieuwe Brusselse kalenderdag, ook bij een feedstoring. Externe reverse-proxy/CDN-caches moeten afzonderlijk cachetags of passende TTL's respecteren.

## Controles

```sh
ddev exec php scripts/check-calendar-id.php
ddev exec php scripts/check-date-label.php
ddev exec php scripts/check-event-ics.php
ddev exec php scripts/check-event-parser.php
ddev exec php scripts/check-agenda-day.php
ddev drush php:script scripts/check-editor-access.php
```

Controleer daarnaast de publieke pagina's op 320, 390, 768, 1440 en 1920 px, menu/Escape, alle inschrijvingsstappen, praktische tabbladen, PDF- en kalenderdownloads, wachtwoordherstel en 404. Verstuur geen testmails of inschrijvingen naar echte ontvangers.

Zie `docs/site-scan-2026-10-05.md` voor de oorspronkelijke scan en `docs/site-scan-uitvoering-2026-10-05.md` voor de uitvoering en grenzen van de controles. Er wordt vanuit deze taak niets gepusht of gedeployd.
