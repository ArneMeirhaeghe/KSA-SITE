# KSA-thema

Het thema gebruikt Drupal Single Directory Components in `components/`. De site bevat een responsive header, hero, nieuws- en leidingsoverzichten, detailpagina's, inschrijvingsstappen, praktische tabbladen, rondeboekjes, agenda, verhuur en contact. Inter wordt lokaal geladen.

`templates/` koppelt Drupal-renderarrays aan componenten. Tekst, media, volgorde, veldformatters en Views blijven via Drupal beheerbaar. `css/tokens.css` bevat de Figma-ontwerpwaarden; `css/layout.css` regelt secties. Layout Builder Styles maakt stappen, tabbladen, boekjeskaders en tekst/fotosecties expliciet instelbaar.

JavaScript gebruikt Drupal behaviors en `once`. De opruiming bij unload is aangevuld voor site-header, media-strip, text-media en jos-login. Zonder JavaScript blijven de inschrijvingsstappen en praktische informatie beschikbaar.

Zie de README in de projectroot voor installatie, configuratiebeheer en controles. Ontwerpbronnen staan in `docs/design-tokens.md` in de projectroot.
