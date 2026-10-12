# Visuele controle — 12 oktober 2026

## Wijzigingen

| Onderdeel | Voor | Na |
|---|---|---|
| Footer | Jos alleen als er elders geen mascotte stond, met een automatisch dagschema | Jos op elke pagina; volgt horizontaal de aanwijzer binnen de footer. Tikken op de strook of toetsenbordbediening werkt ook. Pauze, minder beweging, verborgen tabblad en buiten beeld worden gerespecteerd. |
| Groepskaarten | Afgeronde hoeken op een kader dat breder was dan de foto | Afronding op de volledige foto zelf; gelijke beeldhoogte en linkse uitlijning behouden. |
| Kennismakingsbanner op Ons verhaal | `cover` sneed de groepsfoto bij | De oorspronkelijke beeldverhouding bepaalt de hoogte. Op mobiel staan foto en tekst onder elkaar, zodat gezichten zichtbaar blijven. |
| Mobiele beheerbalk | Lange paginatitels duwden knoppen buiten beeld | Titelkolom kan krimpen; bewerkknop blijft binnen het scherm. |

## Controle

Browsercontrole lokaal op 1280 en 390 pixels: hoofdpagina's, acht gepubliceerde groepspagina's en tien nieuwsdetails; nieuwsarchief pagina 2 meegenomen. Visuele screenshots van de hoofdpagina's en alle groeps- en nieuwsdetails op desktop; mobiele screenshots van de hoofdpagina's en representatieve details. Lagere secties op Rondes, Inschrijving, Ons verhaal en Verhuur bekeken.

Alle 18 groeps- en nieuwsdetails ook op mobiel bezocht en op horizontale overloop en kapotte geladen afbeeldingen gecontroleerd. Extra controle op 320 pixels voor leiding, contact, inschrijving, Leeuwkes Deinze en een lang nieuwsbericht. Geen ontbrekende geladen afbeeldingen gevonden. Mobiele overloop kwam van de beheerbalk en is hersteld.

Footer: lopen via toetsenbord, aanwijzer/tik en pauzeren gecontroleerd in de browser. JavaScript-syntaxis en git-diff gecontroleerd. Er zijn geen inhouds- of configuratiewijzigingen nodig voor deze aanpassingen.

Ook de 31 individuele leiderpagina's buiten de publieke navigatie zijn bezocht op 506 pixels: titel, horizontale overloop en kapotte geladen afbeeldingen gecontroleerd, zonder fouten. Twee representatieve pagina's visueel bekeken; de overige portretten zijn in de groepsweergaven gecontroleerd. De originele aangeleverde uitsnede van een foto kan de website niet herstellen.

## Publicatie

Codecommit `ea68419` naar `main` gepusht en met `git pull --ff-only` op Alwaysdata geplaatst; Drupal-cache vernieuwd. Online leidingsoverzicht, groepsfotobanner en footerbeweging/pauzeknop opnieuw gecontroleerd.
