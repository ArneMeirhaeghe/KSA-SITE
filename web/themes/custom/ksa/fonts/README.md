# Inter

`inter-var.woff2` is ongewijzigd overgenomen uit de geïnstalleerde Drupal-core:
`web/core/modules/navigation/assets/fonts/inter-var.woff2`.

Het thema bewaart een eigen kopie en is daardoor niet afhankelijk van de
Navigation-module of het Gin-thema.

Bronproject en licentie: https://github.com/rsms/inter — SIL Open Font License 1.1.
De licentie staat in `OFL.txt`.

De normale stijl ondersteunt gewichten 100–900 in één bestand.
Er is nog geen apart cursief font; cursief wordt voorlopig door de browser gemaakt.

De site laadt sinds 6 oktober 2026 `inter-latin-var.woff2` (circa 170 KB).
Dit is met FontTools 4.66.1 afgeleid van het originele bestand (circa 337 KB),
met behoud van variabele gewichten, OpenType-features, Latijnse letters,
diakritische tekens, leestekens, valuta en pijlen. De oorspronkelijke kopie
en OFL-licentie blijven bewaard.

```sh
pyftsubset inter-var.woff2 --unicodes='U+0000-024F,U+1E00-1EFF,U+2000-206F,U+20A0-20CF,U+2190-21FF,U+FEFF,U+FFFD' --layout-features='*' --flavor=woff2 --output-file=inter-latin-var.woff2
```
