# Nieuwsimport — 12 oktober 2026

Via de browser geïmporteerd, zowel lokaal als op Alwaysdata. Bron: https://ksadeinze.be/blog en archiefpagina 2.

Vijf nieuwe berichten: Cavabar 2026, Kerstrozen 2024, Startdag 2024, Belgische avond 2024 en Quiz 2024. Publicatiedatums uit de bron behouden; voorbije evenementen als archief beschreven, zonder actieve oude inschrijflinks.

Zeven bestaande berichten aangevuld met hun originele affiche: Quiz 2026, Kerstrozen 2025, Cavabar 2025, Startdag 2025, Ontbijtmanden voor Moederdag 2025, Amerikaanse avond van de Sjo 2025 en Quiz 2025. Alleen het hoofdbeeld is gewijzigd, teksten behouden. Import koppelt op UUID om duplicaten te voorkomen.

Totaal: 15 nieuwsartikels, alle met origineel beeld. Afbeeldingen komen uit `/src/Frontend/Files/Blog/images/source/` op de oorspronkelijke site; geen vervangende stockfoto's of gegenereerde afbeeldingen.

De bestaande `article_wide` beeldstijl sneed nieuw gegenereerde affiches bij. Via afzonderlijke configuratie-import in de browser omgezet naar proportioneel schalen tot maximaal 1600 px breed, zonder vergroten of uitsnijden. Deze YAML is afzonderlijk bijgewerkt in Git.

Ook de responsieve mapping is via de browser aangepast: alle schermgroottes gebruiken nu de bestaande proportionele stijlen `wide` en `large`. Zo kunnen de eerdere bijgesneden desktop-/mobielvarianten niet meer worden gekozen.
