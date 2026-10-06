# Loginpagina

`/user/login`: loginvelden links, Jos rechts. Geen Figma-ontwerp; gebouwd met de site-tokens.
Proof of concept: `prototypes/login/index.html`.

## Wat Jos doet

| Toestand | Wanneer | Jos |
|---|---|---|
| Rust | geen muis, geen veld | ademt, kijkt rond, kwispelt rustig |
| Muis volgen | muis beweegt | hoofd en ogen volgen (tot 20° omhoog, 11° omlaag) |
| Meelezen | Gebruikersnaam actief | leest mee met de cursor, open mond, kwispelt snel |
| Verstoppen | Wachtwoord actief | poten voor de ogen, hoofd stil, gluurt door zijn vingers (ook bij elk teken) |
| Fout | foutmelding na inloggen | schudt "nee", "o"-mond, staart hangt, kijkt naar de melding |

- Fout verdwijnt bij echt typen (niet bij Tab of autofill).
- Minder beweging (`prefers-reduced-motion`): geen animaties, meekijken blijft.

## Bestanden

- `templates/layout/page--user--login.html.twig`: kopie van `page.html.twig`, main content in `ksa:login-page`.
- `components/login-page/`: raster, formulierstijl, meldingen (rood bij fout), mobiel Jos boven (180 px).
- `components/jos-login/`: `jos-login.svg` (= `jos.svg` + gluurpoten `.jos-peek`), css, js (behavior `ksaJosLogin`).
  - Gluurgroep `.jos-peek`: twee vacht-armen (`.jos-peek-arm-l/-r`) + twee poten.
  - Armen in de Jos-stijl: witte handschoen tot de elleboog, dan vacht met verloop, bruine rand, lichtstreep en drie plukjes.
  - Ellebogen voor het lijf, bij de mouwen. Het hoofd staat stil tijdens het verstoppen, zodat de armen vast blijven.
- Foutkleuren (`--color-error`, `--color-error-surface`) staan lokaal in `login-page.css`: nog niet in Figma.

## Drupal-instellingen

- Interface-vertaling: "Log in" → "Inloggen", "Reset your password" → "Wachtwoord vergeten?".
- Core logt in met **gebruikersnaam**, niet met e-mail. E-mail vraagt een contrib-module.

## Let op

- Na css/template-wijzigingen: Prestaties → **Clear all caches**. Controleer de melding "Caches cleared."
- Backups: `backups/2026-09-30-voor-login-fout/`.
