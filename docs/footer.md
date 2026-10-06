# Footer

Op elke pagina, in de vaste footerregio (buiten Layout Builder). Figma: component `Footer` (31:247).
Gebouwd op 30 september 2026. Alle inhoud komt uit twee menu's: aanpassen zonder code.

## Zo beheer je het

Structuur → Menu's.

| Menu | Wat | Voorbeeld |
|---|---|---|
| **Footer** (`footer`) | Kolommen. Item op niveau 1 = kolomkop, items eronder = links. | Over ons → Ons verhaal, Leiding, Rondes |
| **Footer onderaan** (`footer-bottom`) | Kleine links naast het copyright. Nu leeg. | Privacy (zodra die pagina bestaat) |

- **Nieuwe kolom:** Link toevoegen, titel = kop, link = `<nolink>`, "Show as expanded" aan.
- **Nieuwe link:** Link toevoegen, kies de kolom bij **Parent link**.
- **Een pagina linken:** begin de titel van de pagina te typen en kies ze uit de lijst (niet de URL plakken).
  Dan is het een paginalink: wordt de pagina verwijderd, dan verdwijnt de link mee.
  Is de pagina niet gepubliceerd, dan zien bezoekers de link niet.
- **Volgorde:** slepen in het menu, of de gewichten.

## Huidige inhoud

| Over ons | Praktisch | Volg ons |
|---|---|---|
| Ons verhaal (`/node/36`) | Inschrijving & info (`/node/35`) | Facebook (facebook.com/KSADeinzeAstene) |
| Leiding (`/node/33`) | Verhuur (`/node/37`) | |
| Rondes (`/node/34`) | Contact (`/node/38`) | |

- Uit Figma weggelaten: Privacy Policy en Terms of Service (pagina's bestaan niet), Instagram en Ravot (geen URL gekend).
- Onderaan: "© {jaar} KSA Deinze-Astene" (jaar automatisch) en "Naar boven".

## Blokken (Structuur → Blokindeling → KSA → Footer)

| Blok | Systeemnaam | Instellingen |
|---|---|---|
| Footermenu | `ksa_footer_menu` | menu Footer, titel verborgen, 2 niveaus, alle items uitgeklapt |
| Footer onderaan | `ksa_footer_bottom` | menu Footer onderaan, titel verborgen, 1 niveau |

Andere blokken in de footerregio verschijnen onder de kolommen.

## Thema

- `templates/layout/region--footer.html.twig`: embedt `ksa:site-footer` met beide menublokken.
- `templates/navigation/menu--footer.html.twig`: kolommen (h2 + lijst). `menu--footer-bottom.html.twig`: kleine lijst.
- `templates/block/block--system-menu-block--footer(-bottom).html.twig`: zonder blokwrapper.
- `components/site-footer/`: kaart met `assets/ksa-header.png` (onderste deel) + 20 % donker, wit logo (`logo.png`).
- Desktop: kolommen op 411 / 726 / 1040 px zoals in Figma. Mobiel (< 700 px): 2 kolommen, dan lijn, "Naar boven", ©.
- De tijdelijke 50 px onder de laatste sectie (css/layout.css) is weg: de footer zelf heeft 50 px marge boven.

## Gecontroleerd

- Anoniem op `/`, `/ons-verhaal`, `/leiding`, `/node/30`, `/user/login`: footer met 3 kolommen.
- 1280 px: kaart 1216 breed op 32 px, lijn en onderregel op 1–2 px van Figma.
- 402 px: kolommen 2 × 2 op de Figma-posities, geen horizontale overloop.

Backup: `backups/2026-09-30-voor-footer/`.
