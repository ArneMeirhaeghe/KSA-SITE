# Inschrijving & info

De pagina staat op `/inschrijving` (node 35, Basispagina).
Titel: **Hoe schrijf ik in?**. Het menu-item blijft **Inschrijving & info**.
Figma Desktop `103:836` (bouwplan 06, gekozen door Arne) en Mobile `129:2373`.

## Zo beheer je het

| Wat | Waar |
|---|---|
| Titel en intro | Inschrijving → Bewerken → Titel en **Intro** |
| Een stap aanpassen | Layout → sectie **Stappen** → potloodje op de stap → Configure |
| Een tab aanpassen | Layout → sectie **Praktische info** → potloodje → Configure |
| Volgorde | Layout → potloodje → **Move**. De volgorde van de blokken = de volgorde van de stappen/tabs |
| Stap of tab toevoegen | **Add block** → Tekst met beeld, **Stijl** = Stap of Tab |

Het label in de stappenbalk en op de tab is de **Titel** van het blok.
Opeenvolgende blokken met stijl Stap worden één stappenbalk, met stijl Tab één rij tabs.
In de layout-editor en zonder JavaScript staan alle blokken gewoon onder elkaar.

## Inhoud

Stappen (screenshots uit het stappenplan van Ravot, namen en geboortedatum vervaagd):

1. Surf naar 'ravot.ksa.be' (knop Naar Ravot, `https://ravot.ksa.be/nl/auto`)
2. Log in of maak een account aan
3. Schrijf je in bij 'KSA Deinze-Astene'
4. Maak een account aan voor je kind
5. Kies de groep en vul de gegevens in
6. Rond de betaling af (€37), IBAN BE49 8601 0881 3871, knop naar Contact (foto Jos de Vos)

Tabs (teksten van ksadeinze.be/inschrijven, /uniform en /werking):
Verzekering, Mutualiteit, Uniform (knop De Rasp), Financiële ondersteuning, Waar spreken we af? (knop Rondes).
"Grote activiteiten" staat verborgen in Figma en is niet gebouwd.

## Drupal-instellingen

- Tekst met beeld, veld **Stijl**: nieuwe waarden **Stap** (`step`) en **Tab** (`tab`).
- Nieuwe media 32–36 (Ravot-screenshots, glijbaan). Hergebruikt: 10 vlot, 12 Brielhof, 13 bokspringen, 14 Jos, 15 leiding.
- Layout node 35: sectie 1 Intro + lege body; sectie 2 (label Stappen) 6 × Tekst met beeld (Stap);
  sectie 3 (label Praktische info) 5 × Tekst met beeld (Tab).

## Code

- `text-media.twig`: stijl step/tab, `data-text-media-group`; bij stap/tab is de foto inhoud (alt blijft), geen decor.
- `text-media.js` (nieuw): bouwt de stappenbalk (nummers, Vorige/Volgende, "Stap n" op gsm)
  en de tabs (WAI-ARIA, pijltjestoetsen, Home/End). Focus gaat naar de titel van de nieuwe stap.
- Jos de vos staat op het actieve nummer van de stappenbalk en springt van nummer naar nummer
  (één sprong per stap, 0,42 s, boog van 48 px, kijkt in de springrichting). `jos.svg` wordt één keer
  opgehaald; decor, props en extra monden zijn verborgen. Minder beweging: Jos staat meteen op de nieuwe stap.
  Op gsm is de stappenbalk en dus ook Jos verborgen.
- De eigen knop van een stap (Naar Ravot, Contacteer de hoofdleiding) staat in dezelfde rij als
  Vorige/Volgende: links Vorige of de knop, rechts Volgende of de knop. Op gsm krijgt de knop een eigen
  volle rij erboven.
- Twee soorten knoppen: **actie** (de eigen knop van een stap of tab) = groen gevuld en vet,
  met een pijltje ↗ als de link naar een andere website gaat. **Navigatie** (Vorige/Volgende stap) =
  groen omlijnd met een pijltje ‹ ›, zoals in Figma.
- `text-media.css`: stap 621 + 433, tab 528 + 528, stappenbalk, tabs; gsm onder elkaar, tabs scrollen zijwaarts.

Back-up vooraf: `backups/2026-09-30-voor-inschrijven/`. Beelden: `prototypes/inschrijven/beelden/`.

## Ontwerp en afwijkingen

| | Desktop | Mobiel |
|---|---|---|
| Stappenbalk | 6 × vakje 32 (radius 4), actief groen, 19 px onder de intro | verborgen, "Stap n" boven de titel |
| Stap | screenshot 621 × 349, tekst 433, titel 36 | onder elkaar, titel 26, tekst 14 |
| Knoppen | groen omlijnd, radius 8, 48 hoog | 41 hoog |
| Tabs | 16 semibold, 63 px uit elkaar, balk 3 px | zijwaarts scrollen |
| Tab | foto 528 × 268 + tekst, 65 px tot de footer | onder elkaar |

- Paginatitel gecentreerd 64 px (Figma: links, 36 px), zoals de andere basispagina's.
- "Stap n" in donkergroen (#2e7d32): het KSA-groen is te licht voor tekst van 14 px.
- Bouwplan-notitie zei "eerst zonder stappenbalk en tabs"; Arne koos de getekende versie.
  Zonder JavaScript blijft de eenvoudige versie (alles onder elkaar) werken.
- Prijs: **€37** (bevestigd door Arne, 30-09-2026), zoals ksadeinze.be/inschrijven. De PDF, Figma en /werking zeggen nog €35.
