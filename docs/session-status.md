# Sessie-status — KSA Deinze-Astene

Laatst bijgewerkt: 5 oktober 2026.
Branch: geen, dit project heeft geen git.

## Waar we mee bezig zijn
De Google Agenda op de Rondes-pagina. Werkt nu, met "KSA Kalender". Uitleg: `docs/agenda.md`.

## Klaar
- 9 van de 10 ontwerp-pagina's gebouwd. Foto's is buiten scope.
- Module `ksa_agenda` staat aan. Blok "Agenda uit Google" staat in sectie 4 van Rondes (node 34).
- Oud tijdelijk agenda-blok en zijn CSS zijn weg. Backup: `backups/2026-10-05-voor-agenda-module/`.
- `sabre/vobject` geïnstalleerd (3 pakketten, 0 updates).
- Paginering in het Nederlands (`translations/ksa-pager.nl.po`).
- 48 controles groen, in 4 scripts in `scripts/`.
- Paginering van de agenda volgt Figma (frame 121:1947): "‹ Vorige 1 2 3 Volgende ›".
- Kampkaarten (sectie 3) tonen nu de echte weekend- en kampdata per groep (6 van 6 gecontroleerd).
- "Wekelijkse rondes" + rondeboekjes staan ook op de startpagina, als gedeeld blok (Inhoud → Blokken).

## Volgende stap (concreet)
1. Arne kiest uit de scan van de volledige site (22 punten, 4 fases): https://claude.ai/artifact/A3TWDaGdCjTsgnnmaLBCuX
   Daarna fase 1 bouwen (sitenaam, favicon, URL-patronen, Nederlandse teksten + 404, tikfouten).

## Open vragen / blockers
- Logboek 05-10 om 22:38 en 22:45: bezoeken die niet van Claude kwamen. Andere sessie actief op deze site?
- Kaart klikken: eigen agenda (.ics, zo gebouwd) of kalender naar die dag laten springen?
- GIF-regel in de project-CLAUDE.md vervangen door screenshots?
- Rol `content_editor` het recht "Agenda-instellingen beheren" geven (via Chrome).
- PHPUnit installeren, en de 4 scripts omzetten naar echte tests?
- **Arne:** in Google Agenda op Reset drukken bij "Sport schema". De geheime link stond in de chat.
