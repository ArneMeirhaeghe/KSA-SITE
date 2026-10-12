# Wijzigingslog leiding

Beheerpagina: `/admin/reports/leiding`, onder Rapporten → Wijzigingslog leiding.
Gebaseerd op de opslag van Drupal Entity Log, met projectspecifieke snapshots in
KSA Leiding voor leesbare verwijzingen naar foto's/PDF's en Layout Builder-blokken.

- Registreert insert/update/delete van inhoud, blokken, media, bestanden en de
  gewone eigen-profielvelden als de uitvoerende gebruiker `content_editor` heeft.
- Bewaart tijdstip, gebruikersnummer en gebruikersnaam op dat moment, onderwerp,
  veld, taal, revisie en volledige oude/nieuwe waarden. Volgorde telt mee.
- Media bevatten ook de naam en bestandslocatie op het moment van de wijziging.
- Inline blokken worden bij het opslaan van de pagina geregistreerd. Alleen het
  openen of aanpassen van een nog niet opgeslagen formulier is geen wijziging.
- Uploads worden al bij het opslaan van de media geregistreerd, ook als de gebruiker
  vervolgens de pagina-aanpassing annuleert: de media bestaan dan immers al.
- Wachtwoorden, tokens, API-sleutels en IP-adressen worden niet bijgehouden.
- Alle waarden worden ontsmet weergegeven: historische HTML wordt als tekst
  getoond, nooit uitgevoerd.

Filters: gebruiker, soort, onderdeel/veld en vanaf/tot-en-met (tijdzone Brussel).
25 veldwijzigingen per pagina; nieuwste eerst. Vergelijkingen klappen open en
stapelen op kleine schermen onder elkaar.

Alleen beheerders met `administer users` mogen dit overzicht lezen. Leiding
krijgt geen Entity Log-lees-, bewerk- of verwijderrechten. De gewone technische
Drupal-logs blijven afzonderlijk beschikbaar. Standaard is er geen automatische
opruimlimiet voor Entity Log; zet geen `row_limit` als deze historie behouden moet
blijven. Het is een beheerlogboek, geen cryptografisch onveranderbare audittrail:
een beheerder/databasebeheerder kan de opslag nog aanpassen.

De registratie is prospectief. Oude wijzigingen worden niet gereconstrueerd.
Bestandsnamen en locaties worden vastgelegd, niet een extra kopie van de bytes;
een oude bestandslink kan niet meer werken als een admin dat bestand verwijdert
of buiten Drupal overschrijft. Wijzigingen rechtstreeks in SQL/bestanden en in
de externe Google Agenda vallen buiten deze Drupal-opslaghooks.

Activering op een omgeving: Composer-dependencies installeren, vervolgens via
Uitbreiding de module Entity Log (met Dynamic Entity Reference) inschakelen.
Geen brede config-import nodig. De KSA-integratie registreert de velden zelf;
de optionele veldselectie van Entity Log blijft uit om dubbele records te vermijden.

Controles: `ddev drush php:script scripts/check-leadership-log.php` gebruikt een
database-transactie en rolt alle fixtures en logregels terug. Het controleert
o.a. echte opslaan-hooks, oude/nieuwe tekst, foto-volgorde, bestandsnamen,
wachtwoorduitsluiting, autorisatie en veilige weergave. Bestaande algemene
rechtencontrole: `scripts/check-editor-access.php`.
