# Leidingbeheer

## Dagelijks gebruik

Open `/admin/leiding` na het aanmelden. De leiding krijgt vijf onderdelen:

- **Pagina’s:** bestaande teksten en foto’s aanpassen. Kies **Bewerken**, open de inhoud van het gewenste blok, kies **Toepassen** en daarna **Wijzigingen opslaan**. **Titel en intro** opent de gewone pagina-instellingen. Een gedeeld tekstblok wordt via zijn eigen bewerklink aangepast en verandert op alle pagina’s waar het gebruikt wordt.
- **Nieuws:** berichten toevoegen, aanpassen en zichtbaar of verborgen zetten.
- **Leiding:** publieke leidersprofielen toevoegen, foto en telefoonnummer aanpassen en aan een ploeg koppelen. Dit maakt geen inlogaccount aan.
- **Ploegen en rondeboekjes:** bestaande ploeggegevens aanpassen en een nieuwe PDF als rondeboekje koppelen. Onder de groepsnaam staat **Zichtbaarheid van de groep**: kies **Verbergen op de website** en sla op om een groep tijdelijk te verbergen. Kies later **Tonen op de website** om dezelfde groep opnieuw te gebruiken. Verborgen groepen verdwijnen uit de groepenlijst, contactkaarten en rondeboekjes; de gegevens blijven bewaard. +16 staat momenteel verborgen.
- **Foto’s en PDF’s:** bestanden toevoegen en bewerken. PDF’s zijn maximaal 20 MB. Een geüploade PDF moet daarna nog aan de juiste ploeg of inhoud gekoppeld worden.

Activiteiten en datums worden in de gedeelde Google Agenda onderhouden. Drupal-rechten geven geen toegang tot Google Agenda.

Leiding kan ook inhoud van andere leiding aanpassen en verborgen inhoud bekijken. Verouderde leiders of nieuwsberichten kunnen worden verborgen. Definitief verwijderen blijft bij de admin. Afbeeldingen en PDF’s staan in publieke opslag; een verborgen pagina maakt een bestaande bestandslink niet privé.

## Accounts en rollen

- **Administrator:** alle opties en rechten, voor de beheerder.
- **Leiding** (`content_editor`): dagelijks inhoudsbeheer, zonder algemene instellingen, gebruikersbeheer, nieuwe vaste pagina’s/ploegen of verwijderrechten.
- **Bezoeker** (`anonymous`): alleen gepubliceerde inhoud en publieke bestanden bekijken.
- **Authenticated user:** technische basisrol die iedere ingelogde gebruiker er automatisch bij krijgt. Dit is geen vierde gebruikersgroep. De basisrol heeft geen bewerkings- of bestandsverwijderrechten.

De admin maakt persoonlijke accounts via **Gebruikers → Gebruiker toevoegen** en kent alleen **Leiding** toe. Bezoekers kunnen zich niet registreren. Bij vertrek: het inlogaccount blokkeren en eventueel het publieke leidersprofiel verbergen. Alleen een leidersprofiel aanpassen trekt geen inlogrechten in.

## Implementatie

De bestaande rol is via de Drupal-interface aangepast en hernoemd. `Simplify` verbergt technische metadata in formulieren; admins blijven alles zien. `View Unpublished` geeft per inhoudstype toegang tot gedeelde concepten. De inhoudsrechten zijn via de UI opnieuw opgebouwd.

`Layout Builder Advanced Permissions` beperkt toevoegen, verwijderen en verplaatsen van blokken/secties. De kleine module `ksa_editor` vult de ontbrekende projectbeperkingen aan:

- Een startscherm en korte inhouds-/medialijsten zonder bulkacties of verwijderknoppen.
- Een enkele inhoudslink in de navigatie van leiding; beheerders behouden hun volledige navigatie.
- De eigen profielpagina van leiding verwijst naar het startscherm; account bewerken blijft bereikbaar.
- Servercontroles weigeren layout-reset en configuratie van technische blokken, Views en agenda. Alleen bestaande inhoudsblokken op bewerkbare basispagina’s zijn toegelaten.
- Veldtoegang beschermt stijl, achtergrondkleur en technische agendavelden. Bloktitel, titelweergave, view mode en componentstijlen blijven behouden tijdens inhoudsbewerking.
- Gewone node-revisies blijven beschikbaar; ze herstellen de opgeslagen versie inclusief eventuele inhoudsblokken.

De module vult bestaande Drupal-oplossingen aan omdat die alleen niet alle reset-, blokconfiguratie- en interfacebeperkingen bieden. Configuratie blijft via de beheerinterface beheerd.

## Controle op 7 oktober 2026

`ddev drush php:script scripts/check-editor-access.php` voert 383 geslaagde controles uit zonder gebruikers of inhoud op te slaan: inhoudsrechten, publicatiestatus, uploadrechten, bestaande publieke/verborgen nodes, technische routes, alle huidige pagina’s en hun blokacties, beschermde velden, leidersformulier en navigatie.

Het startscherm en de mediaweergave zijn in de browser als admin geopend. Een volledige browsertest als leiding, inclusief opslaan en uploaden, wacht nog op toestemming om een tijdelijk lokaal testaccount aan te maken. Er is geen testaccount aangemaakt en er is geen e-mail verstuurd.

De UI-export `config-ksa-ddev-site-2026-10-07-08-30.tar.gz` is gecontroleerd. Alleen `core.extension`, beide gewijzigde rollen, het PDF-veld en `simplify.global` zijn naar `config/sync` overgenomen. Er is niets gepusht of gedeployd.
