# B05 · Kontaktseite — Spezifikation

Status: `rekonstruiert` · Stand: 2026-09-10 · rückwärts aus dem Bestand erfasst

## Zweck

Besucher finden auf einer eigenen Seite die Kontaktwege zum Betreiber: E-Mail, Standort,
Partei und Social-Media — als direkte Links, **ohne** Eingabeformular.

## Abhängigkeiten

| Braucht | Status | Warum |
|---|---|---|
| B06 Zweisprachigkeit | rekonstruiert | Seite existiert je Sprache (`/lb/contact`, `/en/contact`) |

## User Stories

- **US-01** · Als Besucher möchte ich schnell eine Kontaktmöglichkeit finden, um den
  Betreiber zu erreichen.

## Nicht im Scope

- **Ein datenerfassendes Kontaktformular** — bewusste Entscheidung (Phase 1): Kontakt läuft
  nur über `mailto:` und Social-Links. Ein echtes Formular wäre ein eigenes Feature (mit
  Spam-/Rate-Limit-Schutz und Datenschutzstufe B).

## Akzeptanzkriterien

- **AK-01** · Angenommen, ich öffne `/lb/contact`, dann sehe ich drei Karten (E-Mail als
  `mailto`, Standort „Luxembourg", Partei-Link `dp.lu`) und darunter Social-Media-Icons
  (Facebook, Instagram, LinkedIn) — **kein** Eingabeformular.
- **AK-02** · Angenommen, ich öffne `/en/contact`, dann sehe ich dieselbe Seite mit
  englischen Texten.
- **AK-03** · Angenommen, ich klicke die E-Mail-Karte, dann öffnet sich der Mail-Client mit
  `mailto:email@michael-ferreira.com`.
- **AK-04** · Angenommen, ich klicke die Partei- oder Social-Karte, dann öffnet sich das
  Ziel in einem neuen Tab (`target="_blank"`, `rel="noopener noreferrer"`).

### Datenschutz und Missbrauchsschutz

- §1–§5: **trifft nicht zu** — die Seite erfasst und speichert nichts, sendet nichts an
  Dritte (nur ausgehende Links). §4: kein Eingabekanal, kein Rate Limit nötig.
- §6 Geheimnisse: **trifft nicht zu** — keine Schlüssel im Spiel.

## Edge Cases

- **EC-01** · Die E-Mail-Adresse steht im Klartext im HTML (`mailto`) → für Scraper
  sichtbar (siehe Fehlbestand FB-02).

## Fehlbestand

- **FB-01 · `ContactType` ist toter Code.** `src/Form/ContactType.php` definiert ein
  vollständiges Formular (Name, E-Mail, Betreff, Nachricht + Validierung), wird aber von
  keinem Controller verwendet; `ContactController::index` rendert nur das Template. Auch die
  CSS-Klassen `.form-input`/`.form-label`/`.form-error-message` sind auf dieser Seite
  ungenutzt. Folge: Wartungslast und der irreführende Eindruck, es gäbe ein Kontaktformular.
  **Bewusst mailto-only (Phase-1-Entscheidung) → kein zu bauendes Formular.** Empfehlung:
  `ContactType` entfernen oder als eigenes Feature (Stufe B) neu aufsetzen.
- **FB-02 · E-Mail im Klartext** (Scraping/Spam). Akzeptabel für eine öffentliche
  Kontaktseite, als Verhalten notiert.

## Offene Fragen

- **OF-01** · `ContactType` entfernen oder ein echtes Formular als neues Feature planen?
  Betreiber entscheidet. (Kein Eingriff im Rahmen der Erfassung.)

## Decision Log

| # | Frage | Entscheidung | Begründung |
|---|---|---|---|
| 1 | Kontaktweg | `mailto` + Social statt Formular | kein Spam-/DSGVO-Aufwand, bleibt Stufe A |
| 2 | `ContactType` im Code belassen | (unbeabsichtigter) Überrest | war offenbar geplant, nie verdrahtet — Fehlbestand FB-01 |
