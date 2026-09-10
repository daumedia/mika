# Mika Ferreira — Product Requirements Document

Stand: 2026-09-10 · Stufe Datenschutz: A · Stack-Profil: `symfony-doctrine`
Artefaktpfad: `docs/`

> Rückwirkend aus dem Bestand erfasst (`sdd-erfassen`, Phase 1). Beschreibt, **was der
> Code heute tut**, nicht was er tun sollte. Lücken stehen als *Fehlbestand* in der
> jeweiligen Feature-Spec, nicht hier.

## Vision

Öffentliche zweisprachige Präsenz- und Wahlkampfseite von Mika (Michael) Ferreira,
Kandidat und Mitglied der DP Kayl-Téiteng. Bürgerinnen und Bürger der Gemeinde lernen die
Person, ihre politischen Schwerpunkte (Inklusion, Jugend, Wohnen, Kultur) und aktuelle
Neuigkeiten kennen — auf Luxemburgisch oder Englisch. Der Betreiber pflegt die Neuigkeiten
selbst über einen geschützten Admin-Bereich.

## Zielgruppe

| Gruppe | Situation | Was sie hier will |
|---|---|---|
| Bürger & Wähler Kayl-Téiteng | sucht vor/rund um eine Wahl Informationen zur Person und ihren Positionen | schnell erfassen, wofür der Kandidat steht, und aktuelle Neuigkeiten lesen |
| Interessierte Öffentlichkeit / Presse | stößt über Social Media oder Suche auf die Seite | Kontaktkanäle und Themenprofil finden |
| Betreiber (Mika) | will eine Neuigkeit veröffentlichen | zweisprachigen Artikel anlegen, bearbeiten, löschen — ohne Entwickler |

## Im Scope

- Startseite mit Kurzporträt, vier politischen Themen und Vorschau der neuesten Beiträge
- Neuigkeiten öffentlich lesen — Liste und Einzelansicht, zweisprachig (LB/EN)
- Neuigkeiten im Admin-Bereich anlegen, bearbeiten, löschen (je Sprache getrennt gepflegt)
- Passwortgeschützter Admin-Login für den Betreiber
- Sprachumschaltung LB ↔ EN auf jeder Seite
- Kontaktseite mit E-Mail-, Standort-, Partei- und Social-Media-Verweisen

## Nicht im Scope

- **Kein Spenden- oder Zahlungssystem** (bewusst — keine Zahlungsabwicklung, kein PCI-Umfang).
- **Kein Besucher- oder Mitgliederbereich** (bewusst — nur der Betreiber meldet sich an;
  keine Registrierung, keine Konten für Besucher).
- **Kein funktionierendes Kontaktformular** (bewusste Entscheidung: Kontakt läuft über
  `mailto:`- und Social-Links). Die Klasse `ContactType` samt Formular-CSS existiert im
  Code, wird aber nicht verwendet — toter Code, siehe Feature B05.
- Keine Kommentar-/Community-Funktion und kein Newsletter (nicht gebaut; nicht als
  ausdrückliches Nicht-Ziel bestätigt — bei Bedarf eigenes Feature mit eigener Nummer).

Dieser Abschnitt ist wichtiger als er aussieht: Er ist die Stelle, an der ein Agent
aufhört, Nachbarfunktionen mitzubauen.

## Erfolgskriterien

- Die Seite ist unter der Wahlkampf-Domain erreichbar, in beiden Sprachen fehlerfrei
  bedienbar, und der Betreiber veröffentlicht Neuigkeiten selbstständig ohne Entwickler.
- (offen, mit Betreiber zu quantifizieren: z. B. Seitenaufrufe je Monat, Zahl der
  veröffentlichten Beiträge — aktuell keine Analyse installiert, daher nicht messbar.)

## Rahmenbedingungen

| Thema | Entscheidung |
|---|---|
| Stack-Profil | `symfony-doctrine` — Details in `~/.claude/sdd/stacks/symfony-doctrine.md` |
| Backend | Symfony 8.0 (PHP ≥ 8.4), Doctrine ORM 3, self-hosted MySQL 8 (lokal via Docker Compose) |
| Frontend | Twig, Tailwind CSS v4 (CSS-`@theme`), Stimulus + Turbo (Symfony UX) |
| Asset-Pipeline | **doppelt vorhanden** — Webpack Encore **und** AssetMapper/importmap gleichzeitig aktiv (siehe `app-shell.md`, Fehlbestand) |
| Umgebungen | lokal via Docker Compose (`docker-compose.yml`, MySQL 8); Prod-Umgebung noch nicht dokumentiert |
| Datenregion | offen — Hosting noch nicht festgelegt |
| Sprachen | Luxemburgisch (Standard) + Englisch, per URL-Präfix `/lb`, `/en` |
| Monetarisierung | keine — nicht-kommerzielle politische Selbstdarstellung |
| Externe Dienste | **Google Fonts** (Fraunces, Figtree) hotlinked von `fonts.googleapis.com`/`fonts.gstatic.com` — überträgt Besucher-IP an Google; **Mailer** (`MAILER_DSN`) konfiguriert, aber von keinem Feature genutzt |

## Datenschutz — Kurzfassung

Stufe A, weil die Anwendung **keine Besucherdaten verarbeitet oder speichert**: keine
Besucherkonten, kein datenspeicherndes Formular, keine Analyse-Dienste. Das einzige
gespeicherte „personenbezogene" Datum ist der Login des Betreibers selbst
(`admin.username` + gehashtes Passwort). News-Inhalte sind bewusst veröffentlichte,
öffentliche politische Aussagen.

Für die ganze App gilt:

- Kein Besucher-Tracking, keine Cookies über die Symfony-Session hinaus.
- **Offener Punkt (DSGVO):** Google Fonts werden zur Laufzeit von Google-Servern geladen
  und übertragen dabei die IP jedes Besuchers an Google. Für einen sauberen A-Betrieb
  gehören die Schriften lokal ausgeliefert. Notiert als Muster für den Auditbericht.
- Server-/Zugriffslogs (Infrastruktur) sind nicht Teil des Anwendungscodes und werden im
  Betrieb (`sdd-betrieb`) behandelt.

Sollte je ein datenerfassendes Kontaktformular oder ein Newsletter hinzukommen, steigt die
Stufe auf B — dann greift der volle Katalog.

## Feature-Roadmap (Bestandsinventar)

Alle Einträge sind Bestand (`bestand`) — gebaut, bevor die Kette da war. IDs mit
`B`-Präfix. Voll geführt in `features/index.md`.

| ID | Feature | Prio | Kurzbeschreibung | Abhängig von |
|---|---|---|---|---|
| B01 | Admin-Login | P0 | Betreiber authentifiziert sich, um den Admin-Bereich zu erreichen | — |
| B02 | Neuigkeiten verwalten | P0 | Admin legt zweisprachige Beiträge an, bearbeitet und löscht sie | B01 |
| B03 | Neuigkeiten lesen | P0 | Besucher lesen Beitragsliste und Einzelbeitrag (LB/EN) | B02 |
| B04 | Startseite | P0 | Hero, Kurzporträt, vier Themen, News-Vorschau, Kontaktblock | B03 |
| B05 | Kontaktseite | P1 | E-Mail-, Standort-, Partei- und Social-Verweise (mailto, kein Formular) | — |
| B06 | Zweisprachigkeit (LB/EN) | P0 | Locale-Routing und Sprachumschalter auf jeder Seite | — |

`P0` = ohne das ist es kein Produkt. `P1` = danach.

**Rückerfassungs-Reihenfolge (nach Risiko, nicht nach Nummer):** B01 → B02 → B03 → B06 → B05 → B04

Begründung in `features/index.md`. Kurz: Login und News-Verwaltung tragen Zugriffsregeln
und nehmen Eingaben entgegen — sie kommen zuerst. Der Fixture-Admin mit Passwort `admin`
ist der dringlichste Prüfpunkt.

## Offene Punkte

- **OF-01** (2026-09-10): Prod-Umgebung, Hosting und Datenregion sind nicht dokumentiert
  (`.env` steht auf `APP_ENV=dev`). Vor einem Deploy zu klären.
- **OF-02** (2026-09-10): Erfolgskriterien nicht quantifiziert — keine Produktanalyse
  installiert.
- **OF-03** (2026-09-10): Google-Fonts-Hotlinking (IP-Abfluss) — lokale Auslieferung
  erwägen.
