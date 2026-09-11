# 01 · Rechtstexte (Impressum & Datenschutzerklärung) — Systemdesign

Status: `architected` · Stand: 2026-09-11 · Stack-Profil: `symfony-doctrine`

**Kein Code in diesem Dokument.** Es wird gelesen und freigegeben, nicht ausgeführt.

## Überblick

Zwei rein darstellende, zweisprachige Seiten — Impressum und Datenschutzerklärung — als
statische Twig-Templates. Ein neuer Controller ohne Datenbankzugriff liefert je Seite zwei
Locale-Routen (`/lb/…`, `/en/…`), exakt nach dem Muster der bestehenden Controller
(`HomeController`, `NewsController`). Die eigentlichen Rechtstexte liegen als
sprachspezifische Twig-Inhaltsbausteine; kurze wiederkehrende Strings (Footer-Linktexte,
Verbindlichkeitshinweis, Seitentitel) kommen über die vorhandenen Übersetzungsdateien
`messages.lb.yaml` / `messages.en.yaml`. Der Footer in `base.html.twig` bekommt zwei Links,
und die Sprachpillen-`route_map` wird um die neuen Routen ergänzt, damit die Umschaltung auf
die passende Fassung führt statt auf die Startseite. **Keine neue Entität, keine Migration,
keine externen Aufrufe.**

## Seiten und Routen

| Route | Zweck | Zugang |
|---|---|---|
| `/lb/impressum` (`app_impressum`) | Impressum, luxemburgisch | öffentlich |
| `/en/impressum` (`app_impressum_en`) | Impressum, englisch | öffentlich |
| `/lb/datenschutz` (`app_datenschutz`) | Datenschutzerklärung, luxemburgisch | öffentlich |
| `/en/datenschutz` (`app_datenschutz_en`) | Datenschutzerklärung, englisch | öffentlich |

Gleiche Slugs in beiden Sprachen (Entscheidung zu OF-04, siehe unten). Routen liegen
außerhalb von `^/admin` und sind damit über die bestehende `access_control`-Regel
öffentlich — es braucht keine neue Zugriffsregel.

## Komponentenstruktur

```
LegalController (kein Repository, kein DB-Zugriff)
├── impressum()      rendert legal/impressum.html.twig     (zwei #[Route], _locale lb/en)
└── datenschutz()    rendert legal/datenschutz.html.twig   (zwei #[Route], _locale lb/en)

templates/legal/
├── _layout.html.twig            extends base; Prosa-Container (lesbare Textbreite,
│                                 Fraunces-Überschriften, Figtree-Fließtext); {% block title %};
│                                 blendet bei _locale=en den Verbindlichkeitshinweis ein (AK-06)
├── impressum.html.twig          extends _layout; bindet den Inhaltsbaustein der aktuellen
│                                 Sprache ein
├── datenschutz.html.twig        extends _layout; dito
└── content/
    ├── impressum.lb.html.twig    Rechtsprosa LU: Betreiber, Kontakt, Anschrift, Partei,
    │   impressum.en.html.twig    Inhaltsverantwortung
    ├── datenschutz.lb.html.twig  Rechtsprosa LU: Abschnitte laut docs/datenschutz.md
    └── datenschutz.en.html.twig  englische Fassung

base.html.twig (Shell, geändert)
├── <footer> … + Zeile mit zwei Links „Impressum" · „Datenschutz"  (übersetzte Labels,
│                 path() auf die locale-passende Route)  → erscheint auf jeder öffentlichen Seite
└── Sprachpille (Desktop + Mobile, je LU-/EN-Zweig): route_map um die vier neuen Routen ergänzt
```

Der Inhaltsbaustein wird über den aktuellen `_locale` ausgewählt; fehlt eine Sprachfassung,
wird auf die luxemburgische zurückgegriffen (Robustheit, EC-02).

## Datenmodell

**Keine Tabelle, kein Feld, keine Migration.** Das Feature ist rein darstellend.

Die Impressum-Angaben sind **Inhalt**, kein Datensatz — sie stehen an einer Stelle im
LU/EN-Inhaltsbaustein (bzw. als kleiner Satz Template-Parameter), damit sie einmal gepflegt
werden. Erfassbare „Felder" nur zur Klarheit, welchen Inhalt AK-07 verlangt:

| Inhaltselement (Impressum) | Pflicht | Herkunft |
|---|---|---|
| Name des Betreibers (Mika/Michael Ferreira) | ja | Betreiber (OF-01) |
| Kontakt-E-Mail (als `mailto`) | ja | vorhandene Kontaktadresse (`base.html.twig`-Footer) |
| Postanschrift | ja | Betreiber (OF-01) |
| Parteizugehörigkeit (DP Kayl-Téiteng) | ja | fest |
| Verantwortlich für den Inhalt | ja | Betreiber (i. d. R. dieselbe Person) |

Abschnitte, die die Datenschutz-Seite enthalten muss (AK-08/09), gespiegelt aus
`docs/datenschutz.md`: Verantwortlicher · keine Tracker/keine einwilligungspflichtigen
Cookies · Server-/Zugriffslogs · Fehler-Tracking Sentry (EU) · Hosting · `mailto`-Kontakt
(kein Formular) · Betroffenenrechte inkl. Beschwerde bei der **CNPD**.

## Zugriffsregeln

| Wer | Darf lesen | Darf schreiben | Erzwungen durch |
|---|---|---|---|
| jeder Besucher (auch nicht angemeldet) | beide Seiten, beide Sprachen | — | öffentlich, da außerhalb `^/admin`; keine neue Regel nötig |
| Betreiber (Admin) | dieselben Seiten | Text nur durch Deploy (statisch) | — |

Keine objektbezogene Zugriffsprüfung, kein Voter — es gibt keinen Datensatz.

## Missbrauchsschutz

| Endpunkt | Limit | Verhalten bei Überschreitung | Wo konfiguriert |
|---|---|---|---|
| beide Rechtsseiten | keins nötig | — | — |

Begründung: nur-lesende, statische Seiten ohne Eingabe, ohne Kosten, ohne externen Aufruf
(Spec Katalog-Abschnitte 4 & 6 „trifft nicht zu"). Die global gesetzten Security-Header aus
`SecurityHeadersSubscriber` (sdd-betrieb) gelten automatisch auch hier. Optional möglich,
aber nicht gefordert: HTTP-Cache-Header (die Inhalte sind praktisch unveränderlich).

## Externe Dienste

| Dienst | Wofür | Was geht hin | Was wird vorher entfernt |
|---|---|---|---|
| — | — | — | — |

Die Seiten rufen **keinen** externen Dienst auf. (Die Datenschutzerklärung *beschreibt*
Sentry und das Hosting, kontaktiert sie aber nicht.) Schriften werden lokal ausgeliefert
(sdd-betrieb) — kein Google-Fonts-Abfluss auf diesen Seiten.

## Technische Entscheidungen

| # | Entscheidung | Alternative | Warum so |
|---|---|---|---|
| 1 | Statische Twig-Seiten, keine Entität/Migration | Admin-CRUD (eigene Tabelle) | Rechtstexte ändern sich selten; CRUD wäre Overengineering (Spec Decision 1) |
| 2 | Rechtsprosa als locale-spezifische Twig-Bausteine; kurze Strings in `messages.*.yaml` | ganze Texte als Übersetzungsschlüssel | Lange Prosa gehört nicht in Übersetzungs-YAML; folgt dem Projektmuster „Seiteninhalt in Twig, UI-Strings in Übersetzungen" |
| 3 | Zwei Routen je Seite mit `_locale`-Default, Namen `app_impressum(_en)` / `app_datenschutz(_en)` | eine Route + Sprachparameter | Exakt das Muster von `HomeController`/`NewsController` — kein zweites Routing-Muster (Regel 3) |
| 4 | Gleiche Slugs in beiden Sprachen (`impressum`, `datenschutz`) | `imprint`/`privacy` für EN | Einfachere `route_map`, wiedererkennbar; kein Mehrwert der Übersetzung (entscheidet OF-04) |
| 5 | Ein gemeinsames `_layout`-Partial (Prosa-Container + EN-Hinweis) | pro Seite eigenes Layout | DRY; der Verbindlichkeitshinweis (AK-06) an genau einer Stelle |
| 6 | Footer-Links + `route_map`-Erweiterung in `base.html.twig` | separate Rechts-Navigation | Footer ist auf jeder öffentlichen Seite da; die Pille braucht die Routen in beiden Zweigen (Desktop+Mobile), sonst Fallback auf Startseite (EC-01) |
| 7 | Kein Cookie-Banner | Consent-Tool | Keine einwilligungspflichtigen Cookies/Tracker (Spec Nicht-Scope) |

Keine neue Bibliothek. Alles mit vorhandenem Symfony-8/Twig-/Übersetzungs-Bestand
(gegen die Nachbar-Controller und `base.html.twig` in dieser Session geprüft).

## Abdeckung der Akzeptanzkriterien

| AK | Erfüllt durch | Anmerkung |
|---|---|---|
| AK-01 | Footer-Zeile in `base.html.twig` mit zwei übersetzten Links auf die locale-passenden Routen | erscheint auf jeder öffentlichen Seite |
| AK-02 | `LegalController::impressum` + Route + `legal/impressum.html.twig` | 200, kein Fallback |
| AK-03 | `LegalController::datenschutz` + Route + `legal/datenschutz.html.twig` | 200 |
| AK-04 | je zwei Routen mit `_locale`-Default; Inhaltsbaustein nach `_locale` | LU unter `/lb/…`, EN unter `/en/…` |
| AK-05 | `route_map`-Erweiterung (LU- und EN-Zweig, Desktop + Mobile) um die vier Routen | Pille schaltet auf die Gegenfassung derselben Seite |
| AK-06 | `_layout.html.twig` blendet bei `_locale=en` den Hinweis „LU-Fassung maßgeblich" ein (Übersetzungsschlüssel) | |
| AK-07 | Inhaltsbaustein `content/impressum.<locale>.html.twig` mit Name, `mailto`-E-Mail, Postanschrift, Partei, Inhaltsverantwortung | Werte aus OF-01 |
| AK-08 | Inhaltsbaustein `content/datenschutz.<locale>.html.twig` mit den gespiegelten Abschnitten inkl. Sentry (EU) und CNPD | Quelle: `docs/datenschutz.md` |
| AK-09 | derselbe Baustein, inhaltlich gegen `docs/datenschutz.md` gehalten; kein Google-Fonts-/Newsletter-/Formular-Text | hängt an OF-02 (Hosting) |
| AK-10 | keine `access_control`-Regel (Routen außerhalb `^/admin` = öffentlich) | ohne Login erreichbar |
| AK-11 | `{% block title %}` je Template über Übersetzungsschlüssel (z. B. „Impressum — Mika Ferreira") | sprachrichtig |
| AK-12 | Impressum-Baustein enthält ausschließlich die freigegebenen Betreiber-Angaben; keine Dritt-Daten | einzige PII-Veröffentlichung |
| Katalog 4 (Missbrauch/Kosten) | „trifft nicht zu" — statische Seiten, keine Eingabe/Kosten | im Abschnitt Missbrauchsschutz belegt |
| Katalog 6 (Geheimnisse) | „trifft nicht zu" — keine Schlüssel | keine externen Aufrufe |

## Offene Fragen (unverändert aus der Spec, entscheidet der Betreiber)

- **OF-01** · konkrete Impressum-Angaben (Name, E-Mail, Postanschrift) — vor dem Bau.
- **OF-02** · endgültiger Hosting-Anbieter + Datenregion — für die Richtigkeit der
  Datenschutz-Seite (AK-09); hängt an PRD OF-01.
- **OF-03** · Rechtsprüfung der Texte durch Fachperson/CNPD.
- **OF-04** · in diesem Entwurf entschieden: gleiche Slugs in beiden Sprachen (Entscheidung 4).
