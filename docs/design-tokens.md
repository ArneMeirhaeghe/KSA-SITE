# Basis voor het KSA-thema

Status: ontwerpwaarden geïnventariseerd; technische themabasis klaar, nog niet actief.
Bron: lokale stijlen in [Figma](https://www.figma.com/design/fjlPw9tE1afoSAoD7PBjy0/KSA-Deinze-Astene), uitgelezen op 28 september 2026.

Een token is een naam voor een vaste ontwerpwaarde.
Bijvoorbeeld: `--color-brand` voor het KSA-groen.
Deze waarden staan centraal in `web/themes/custom/ksa/css/tokens.css`.

## Kleuren — gecontroleerd in Figma

| Figma-stijl | Waarde | Voorgestelde CSS-naam |
| --- | --- | --- |
| Primary | #54C44E | `--color-brand` |
| Dark | #1E1E1E | `--color-text` |
| Dark Grey | #5D5D5D | `--color-text-muted` |
| Grey | #9E9E9E | `--color-grey` |
| Light Grey | #D9D9D9 | `--color-border` |

Voorstel voor gebruik:

- Donkere tekst voor gewone inhoud.
- Dark Grey voor bijkomende informatie.
- Primary voor accenten en knopachtergronden, met donkere knoptekst.
- Grey niet gebruiken voor kleine tekst op wit.

Berekening van contrast op effen kleuren:

| Combinatie | Contrast |
| --- | --- |
| Primary / wit | 2,24 : 1 |
| Dark / Primary | 7,45 : 1 |
| Grey / wit | 2,68 : 1 |
| Dark Grey / wit | 6,58 : 1 |

Hero en footer gebruiken de afbeelding `Ksa_header`, met daarboven
`#1E1E1E` op 20% dekking. De hero heeft daaronder ook `#5FA95B`.
Die basiskleur vervangt de afbeelding niet.
De paarse, blauwe en oranje planningskaders zijn geen themakleuren.

## Tekst — gecontroleerd in Figma

Lettertype: **Inter**.
Gewichten: **400** voor gewone tekst; **600** voor koppen.

| Stijl | Grootte | Regelhoogte in Figma |
| --- | --- | --- |
| h1 | 64 px | Auto |
| h2 | 48 px | 64 px |
| h3 | 36 px | Auto |
| h4 | 32 px | Auto |
| h5 | 24 px | 24 px |
| h6 | 20 px | Auto |
| p | 16 px | 24 px |
| small | 12 px | Auto |

**Auto is niet hetzelfde als 100%.** De regelhoogtes voor CSS moeten nog worden vastgelegd en gecontroleerd met Inter.

Deze waarden zijn de bestaande stijlen. Ze zijn nog geen volledige mobiele typeschaal.
De namen h1–h6 bepalen het uiterlijk; HTML-koppen kiezen we volgens de inhoudsstructuur.

## Regions — afgesproken

| Region | Inhoud |
| --- | --- |
| `header` | Logo, navigatie en actieknoppen |
| `content` | Binnen `main`: titel, inhoud, Layout Builder en Views |
| `footer` | Logo, footermenu’s en sociale links |

Geen aparte region per component. Paginatitel buiten Layout Builder.

## Extra metingen in Figma Web

Op 28 september 2026 rechtstreeks in de eigenschappen gelezen.
Arne vroeg om Figma Web te gebruiken wegens de MCP-limiet.

| Onderdeel | Gemeten waarde |
| --- | --- |
| Desktop Home | 1280 px breed |
| Hero-achtergrond | 1216 × 574 px; x: 32, y: 24; radius 16 px |
| Footer | 1216 × 300 px; x: 32; radius 12 px |
| Footer binnenruimte | boven/links/rechts 32 px, onder 24 px |
| Desktop nieuwscontainer | 1079 px breed; x: 100; tussenruimte 25 px |
| Mobiele Home | 402 px breed |
| Mobiele hero-titel | Inter 600, 34 px, Auto |
| Mobiele hero-intro | Inter 400, 14 px, Auto |
| Mobiel nieuws | 369 px breed; x: 17; één kolom; tussenruimte 36 px |

Bronnen: [desktop hero](https://www.figma.com/design/fjlPw9tE1afoSAoD7PBjy0/KSA-Deinze-Astene?node-id=44-402),
[footer](https://www.figma.com/design/fjlPw9tE1afoSAoD7PBjy0/KSA-Deinze-Astene?node-id=44-366),
[nieuwsraster](https://www.figma.com/design/fjlPw9tE1afoSAoD7PBjy0/KSA-Deinze-Astene?node-id=44-265),
[mobiele Home](https://www.figma.com/design/fjlPw9tE1afoSAoD7PBjy0/KSA-Deinze-Astene?node-id=121-1758),
[mobiel nieuws](https://www.figma.com/design/fjlPw9tE1afoSAoD7PBjy0/KSA-Deinze-Astene?node-id=121-1947).

## Kleine keuzes voor de code

- Container: 1080 px, afgerond van de gemeten 1079 px.
- Mobiele zijruimte: 16 px; het nieuwsraster heeft links 17 en rechts 16 px.
- Maten staan in rem, omgerekend met 16 px als standaard.
- Figma Auto krijgt voorlopig CSS `normal`; controleren met het lokale font.
- Geen vaste sectiehoogtes overnemen: inhoud moet kunnen groeien.
- Mobiele hero-maten gelden voor de hero, niet automatisch voor alle koppen.

## Nog te doen bij de componenten

- Inter is lokaal toegevoegd. Regelhoogtes controleren in de echte componenten.
- De originele achtergrondafbeelding exporteren.
- Knoppen en kaartafrondingen meten bij het bouwen van die componenten.
- Breakpoints kiezen en testen; Figma geeft schermen, geen CSS-breakpoints.

Het minimale thema staat in `web/themes/custom/ksa`. De tokens zijn ingevuld.
De basisstijl gebruikt nu de tokens voor tekst en achtergrond.
De componenten krijgen hun eigen koppeling bij het bouwen.
Geen Sass, buildtool of extra module toevoegen alleen om tokens te gebruiken.
