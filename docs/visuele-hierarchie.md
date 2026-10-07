# Visuele hiërarchie — 6 oktober 2026

Op verzoek alle publieke pagina's nagelopen en de bestaande vormgeving verfijnd.
De grotere navigatie, responsive herofoto's en witte buitenmarges blijven behouden.

## Aanpassingen

- Hoofdtitels: gedeelde schaal van 36 tot 56 px, compacte regelhoogte en gebalanceerde regels. De homepage behoudt zijn grotere herotitel.
- Intro's: 18 px op desktop en 16 px op mobiel, met regelhoogte 1,5. Praktische teksten en informatietegels krijgen ook op mobiel 16 px.
- Sectiekoppen: 28 tot 36 px. Nieuwskaarten krijgen 24 px en ploegkaarten 28 px op desktop, 20 px op mobiel. Meer rijafstand tussen compacte ploeg- en leiderkaarten.
- Home en nieuwsdetails: zichtbare koppen Nieuws en Meer nieuws, met h3 voor de onderliggende artikelkaarten.
- Ons verhaal: de bestaande Drupal-paginatitel is weer zichtbaar boven de fotostrook. De afstand boven de strook is verkleind.
- Rondes en Verhuur: minder losse witruimte tussen een sectiekop en zijn kaarten; de inleidende rondetekst is begrensd op een leesbare regellengte.
- Inschrijving: grotere mobiele leestekst en bediening, ombrekende tabbladen op tablet, meer onderscheid tussen inschrijvingsstappen en praktische informatie door sectieafstand.
- Contact: langere kaartkoppen krijgen een ruimere regelhoogte. Leiderdetails onderscheiden veldlabels van de contactgegevens.
- Nieuwsdetail: bredere titelkolom, minder ruimte vóór het beeld en de volledige affiche binnen een begrensd beeldvlak.

## Drupal-instelling

Via **Structuur → Media types → Image → Manage display → Artikelbeeld** is
voor Afbeelding de responsive beeldstijl gewijzigd van Artikelbeeld naar de
bestaande stijl Foto. Die schaalt zonder bijsnijden; CSS gebruikt `contain`.
Laden blijft eager. De oude beeldstijlen zijn niet verwijderd.

Dit is lokaal opgeslagen in actieve Drupal-configuratie. Er is volgens de
projectafspraak geen configuratie-export uitgevoerd; de opgeslagen snapshot
in `config/sync` moet bij de volgende goedgekeurde export worden bijgewerkt.

## Controle

- Alle 50 gepubliceerde inhoudspagina's (8 basispagina's inclusief foutinhoud, 8 ploegen, 31 leiders, 3 nieuwsberichten) in de browser gecontroleerd op 390 en 1440 px: één zichtbare h1, geen horizontale overloop of Drupal-foutmelding.
- Visuele screenshots beoordeeld voor alle hoofdpagina's en representatieve ploeg-, leider- en nieuwsdetails. De overige details gebruiken dezelfde componenten en zijn via hun gerenderde koppen en afmetingen gecontroleerd.
- Aanvullend 13 routes op 320 en 768 px, inclusief login, wachtwoordherstel en echte 404-route. Overloop door lange woorden op Verhuur gevonden en opgelost met een begrensd kaartraster en woordafbreking.
- Alle zes inschrijvingsstappen en vijf praktische tabbladen gecontroleerd op 320 px; geen overloop.
- Volledige nieuwsaffiche na de beheerwijziging visueel en via geladen afbeeldingsbron bevestigd.
- `git diff --check` geslaagd. Geen berichten verzonden of externe inschrijvingen/boekingen uitgevoerd.

Bewijs: `qa/hierarchy-audit-2026-10-06.json`, `qa/hierarchy-edge-audit-2026-10-06.json`,
`qa/hierarchy-home-desktop.png`, `qa/hierarchy-story-mobile.png`,
`qa/hierarchy-article-desktop.png` en de screenshots van de beeldinstelling.

## Aanvulling — subtiele diepte, 7 oktober 2026

Op expliciet verzoek extra kaartafbakening toegevoegd bovenop de Figma-basis:

- Nieuws en ploegen: witte kaart, fijne donkere rand op 12% dekking, zachte tweedelige schaduw en ingesprongen tekst. Nieuwskaarten en leeslinks staan gelijk binnen een rij.
- Klikbare nieuws- en ploegkaarten: groene rand en iets diepere schaduw bij hover en toetsenbordfocus; geen verplaatsing. Reduced motion schakelt de overgang uit.
- Leiderportretten: alleen het beeld krijgt een fijne rand en zachte schaduw, zodat de compacte contactgegevens ruim blijven.
- Praktische informatie en voorzieningen: zachte schaduw; tekstkaarten, contacttegels en de rondeboekjescontainer krijgen een dun groen bovenaccent.
- Contactlogo's: dekking verlaagd van 20% naar 12% voor rust achter de tekst.
- Nieuwe tokens voor rand en schaduw zijn expliciete ontwerpverfijningen, geen nieuw gemeten Figma-waarden.

Controle: vijf hoofdpagina's op 320, 768 en 1440 px; geen kaarten buiten het scherm. Nieuwsdesktop en groepen/contact op mobiel visueel beoordeeld. De bestaande ingelogde Drupal-beheerbalk loopt op smalle schermen wat door; de publieke kaarten blijven binnen de pagina. Geen content- of configuratiewijzigingen nodig.
Bewijs: `qa/card-accents-checks.json`, `qa/card-accents-desktop.png`, `qa/card-accents-mobile.png`.

## Herstel richting Figma — 7 oktober 2026

Op verzoek van Arne de hierboven beschreven extra diepte weer verwijderd: nieuws en ploegen zijn opnieuw open, de extra kaartschaduwen, groene bovenranden en hover-schaduwen zijn weg. Bestaande dunne grijze kaders bij praktische informatie blijven. De extra schaduw/randtokens zijn verwijderd.

Figma `107:2680` opnieuw uitgelezen met design context en screenshot. Paginakoppen terug naar 48 px met 64 px regelhoogte op desktop, intro naar 16 px. De gekleurde annotatierechthoeken in de huidige Figma-screenshot zijn beoordelingsmarkeringen en niet overgenomen. Bestaande grotere navigatie, mobiele leesbaarheid, dynamische agenda, afbeeldingen en toegankelijke bediening zijn behouden; dit is geen volledige pixel-perfect terugzetting van de hele site.

Via Drupal UI: persoonlijke paginatitels hersteld voor leiding, verhaal, verhuur en contact; Rondes heet opnieuw “Activiteiten & kampen”. Intro's ingekort. Het gedeelde rondeblok heet “Wekelijkse rondes” en gebruikt kortere KSA-taal. Technische SEO, redirects, rechten en ongepubliceerde +16 behouden. Deze latere tekstkeuzes vervangen de betreffende na-voorbeelden uit het SEO-verslag.

Zes routes op 390 en 1280 px gecontroleerd: geen kaartoverloop, geen extra kaartschaduwen. Rondes visueel beoordeeld; alle zeven groepslogo's laden. PHP/config niet gewijzigd. Bewijs: `qa/design-herstel-checks.json`, `qa/design-herstel-rondes.png`.
