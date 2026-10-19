# Nieuwsbrief beheren in Drupal

De nieuwsbrief blijft een eigen privé-contententity, zonder editie- of publicatiestatus. Bestaande IDs, UUIDs, fotoreferenties, instuurdatums en openbare foto-URL’s blijven behouden.

| Wat | Waar |
|---|---|
| Artikelen zoeken en bewerken | Inhoud → Nieuwsbriefartikelen (`/admin/content/nieuwsbrief`) |
| Vorige versie bekijken of herstellen | Artikel → Meer acties → Revisies |
| Extra velden toevoegen | Structuur → Nieuwsbrief → Velden beheren |
| Adminformulier indelen, widgets kiezen | Structuur → Nieuwsbrief → Formulierweergave beheren |
| Artikelweergave indelen | Structuur → Nieuwsbrief → Weergave beheren |
| Kolommen, sortering en filters | Structuur → Overzichten → Nieuwsbriefartikelen |
| Werkbalk en toegestane HTML | Instellingen → Inhoud schrijven → Tekstformaten en editors → Nieuwsbrief |
| Fotogrootte en aanvraag-/instuurlimieten | Structuur → Nieuwsbrief |
| Foto publiceren, nieuwe kopie maken | Artikel → Meer acties → Foto’s publiceren |
| Publicatiegegevens van een foto | Privé-Nieuwsbrieffoto bewerken in Media |
| API-sleutel / instuurlink | Links boven het artikelenoverzicht |
| Rollen en rechten | Gebruikers → Toegangsrechten |

## Rechten

Afzonderlijke rechten bestaan voor lezen, bewerken/herstellen, verwijderen, foto’s publiceren, API-beheer, instellingen/instuurlink en insturen. Het bestaande volledige nieuwsbriefbeheerrecht blijft een overkoepelend adminrecht. Wijs voor een nieuwsbriefredacteur zowel lezen als bewerken toe. Geef publicatie- en API-rechten alleen aan wie die nodig heeft. Gewone leiding behoudt uitsluitend insturen; bezoekers met een instuurlink kunnen geen artikelen lezen.

Drupal beheert veldrechten, formulierwidgets, tekstfilters, revisies, Media en Views. Het publieke instuurformulier blijft bewust een beperkt formulier met instuurlink, CSRF, Honeypot en uploadcontroles vóór opslag. Extra beheervelden worden dus niet automatisch publiek opvraagbaar of invulbaar. De API behoudt ook een expliciete selectie velden; een nieuw beheerveld toevoegen publiceert het niet in de koppeling.

De gerichte alleen-lezen API en het maken van onveranderlijke fotokopieën blijven maatwerk. Er worden geen brede REST/JSON:API-rechten op alle content aangezet. Drupal State bewaart de sleutelhash buiten exporteerbare configuratie; de koppeling tussen privéfoto en openbare kopie gebruikt Drupal KeyValue. De openbare kopie zelf is een standaard Media-item. Verwijder of vervang de bestanden van verzonden nieuwsbrieven niet.

## Migratie en uitrol

1. Maak een databaseback-up. Schakel op de testsite tijdelijk onderhoud in via de admin.
2. Plaats de modulecode en vernieuw caches.
3. Voer `/update.php` uit. Drupal migreert de bestaande entiteit naar revisietabellen, bewaart legacy `body` en kopieert de inhoud naar het standaard `text_long`-veld `content`. De eerste revisie krijgt de oorspronkelijke instuurdatum. Geen artikelen of bestanden verwijderen.
4. Importeer via Single import de nieuwe moduleconfiguratie `filter.format.newsletter`, `editor.editor.newsletter`, `views.view.newsletter_articles` (in die volgorde). Alleen bij de eerste uitrol; latere updates mogen adminaanpassingen niet overschrijven.
5. Geef Anonymous user en Authenticated user gebruiksrecht op het veilige formaat Nieuwsbrief via het tekstformaatbeheer. Geef Leiding het recht ‘Nieuwsbriefartikelen insturen zonder instuurlink’. Dit vervangt de eerdere rolnaamcontrole.
6. Open Structuur → Nieuwsbrief en sla de gewenste instellingen op. Gebruik de standaard Media Library-widget voor foto’s in het adminformulier.
7. Controleer insturen, tekstweergave, Views, revisies en rechten. Schakel onderhoud uit.

Checks: `scripts/check-newsletter.php`, `check-newsletter-validation.php`, `check-newsletter-api.php`, `check-newsletter-public.php`, `check-newsletter-admin.php` via `drush php:script`. API/publicatietests gebruiken tijdelijke sleutels en transacties; voer ze niet tegelijk met echt sleutelbeheer uit. Uploadtests gebruiken uitsluitend synthetische beelden.
