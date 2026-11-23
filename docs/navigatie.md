# Navigatie beheren

Alles is ingesteld via de Drupal-admin, zonder extra code of modules.

## Pagina’s en adressen

| Pagina | Leesbare URL | Interne pagina |
|---|---|---|
| Leiding | `/leiding` | `/node/33` |
| Rondes | `/rondes` | `/node/34` |
| Inschrijving & info | `/inschrijving` | `/node/35` |
| Ons verhaal | `/ons-verhaal` | `/node/36` |
| Verhuur | `/verhuur` | `/node/37` |
| Contact | `/contact` | `/node/38` |
| Home (voorpagina) | `/` | `/node/39` |

Voorpagina: Instellingen → Systeem → Basis site-instellingen → **Standaard voorpagina** = `/node/39`.

Dit zijn lege Basispagina’s. Ze zijn op de lokale site gepubliceerd zodat
de navigatie werkt. De inhoud bouwen we later met Layout Builder.
Op Leiding en Rondes kunnen we Views-blokken plaatsen.

## De Drupal-manier

Een pagina heeft een vast nummer. `/node/36` is de interne route.
`/ons-verhaal` is de leesbare URL-alias van dezelfde pagina.
Er wordt dus geen tweede pagina gemaakt.

Menu-items verwijzen naar de pagina, niet naar een hard gecodeerd domein.
Drupal toont bij het renderen de alias. Als de alias verandert, blijft
de verwijzing naar de pagina werken.

Gebruik korte adressen, kleine letters en streepjes tussen woorden.
Gebruik geen spaties, hoofdletters of `&` in het adres.
De paginatitel mag wel gewoon `Inschrijving & info` zijn.

## Zelf een pagina toevoegen

1. Ga naar **Inhoud → Inhoud toevoegen → Basispagina**.
2. Vul de titel in.
3. Open rechts **URL-alias**, bijvoorbeeld `/ons-verhaal`.
4. Open **Menu-instellingen** en vink **Voer een menulink in** aan.
5. Kies Hoofdnavigatie en sla op.

Sinds 17 november 2026 is het beheer in het Nederlands. Het inhoudstype heette eerst "Basic page".

Voor de twee knoppen: **Structuur → Menu’s → Headeracties**.
Kies bij Link de pagina via het zoekveld.
Contacteer ons verwijst naar Contact. Inschrijven verwijst voorlopig naar
Inschrijving & info.

## Volgorde

Hoofdnavigatie gebruikt gewichten 0, 10, 20, 30 en 40.
Een lager getal staat eerst. Tussenruimte maakt later invoegen eenvoudig.
Home staat uit: het logo gaat al naar de homepage.
Foto’s is niet ingevuld.

## Voettekst

**Structuur → Menu's → Voettekst.** Drie kolommen: Over ons, Praktisch en Volg ons.
Een nieuwe link hang je onder één van die drie (veld **Bovenliggend onderdeel**).
Sinds 16 november 2026 staat **Nieuws** onder Volg ons. Zo blijft de voettekst even hoog.

## Twee pagina's zitten vast in de code

Het thema kent twee pagina's aan hun nummer. Verander of verwijder ze dus niet zomaar.

| Nummer | Pagina | Waarvoor |
|---|---|---|
| `/node/33` | Leiding | De link "← Alle leiding" op een ploegpagina, en het actieve menu-item. |
| `/node/35` | Inschrijving & info | De groene knop. Die volgt de bestemming, niet de plaats in het menu. |

Wijst de knop **Inschrijven** later naar een andere pagina? Dan verliest hij zijn groene kleur
tot het nummer in `templates/navigation/menu--header-actions.html.twig` mee verandert.
Controleer op productie dat deze twee nummers dezelfde pagina's zijn.

## Later een URL wijzigen

De menulink blijft gekoppeld aan de pagina. Een oud gedeeld webadres volgt
echter niet vanzelf mee. Bij een live site moet voor dat oude adres een
redirect worden voorzien. Sinds 7 oktober 2026 beheert de module Redirect dit. Er zijn 24 permanente doorverwijzingen toegevoegd voor oude pagina’s, nieuwsberichten en PDF’s; zie `seo-uitvoering-2026-10-07.md`.

Voor deze paar vaste pagina’s vullen we de alias handmatig in.
Automatische patronen voor veel nieuwsberichten kunnen later apart worden ingesteld.
