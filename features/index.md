# Features

Stand: 2026-09-10 · Stack-Profil: `symfony-doctrine` · Artefaktpfad: `docs/`

Bestandsinventar, rückwärts aus dem Code erfasst (`sdd-erfassen`). Phase 1 (Kartierung)
und Phase 2 (Rückerfassung) sind **abgeschlossen**: jedes Feature hat `spec.md` und
`design.md`, alle stehen auf `rekonstruiert`. IDs mit `B`-Präfix. Nächster Schritt ist die
QA je Feature (`/sdd-qa BNN`), danach der Auditbericht (`/sdd-erfassen abschluss`).

| ID | Feature | Prio | Status | Abhängig von | Zuletzt |
|---|---|---|---|---|---|
| B01 | Admin-Login | P0 | approved | — | 2026-09-10 · QA grün nach Fix (Throttling, Fixture, Logout-CSRF) |
| B02 | Neuigkeiten verwalten | P0 | approved | B01 | 2026-09-10 · QA grün nach Fix (Slug-UniqueEntity) |
| B03 | Neuigkeiten lesen | P0 | approved | B02 | 2026-09-10 · QA grün nach Fix (Datumsfilter show) |
| B04 | Startseite | P0 | review | B03, B06 | 2026-09-10 · QA: production-ready, 1 niedrig |
| B05 | Kontaktseite | P1 | review | B06 | 2026-09-10 · QA: production-ready, 1 niedrig |
| B06 | Zweisprachigkeit (LB/EN) | P0 | review | — | 2026-09-10 · QA: production-ready, 1 niedrig |

## Wo die Features im Code leben

| ID | Zweck (ein Satz) | Kernstellen |
|---|---|---|
| B01 | Betreiber meldet sich an, um den Admin-Bereich zu erreichen | `SecurityController`, `Entity/Admin`, `AdminRepository`, `config/packages/security.yaml`, `Command/CreateAdminCommand`, `templates/security/login.html.twig` |
| B02 | Admin legt zweisprachige Beiträge an, ändert und löscht sie | `AdminController`, `Form/NewsType`, `templates/admin/*`, `Entity/News` |
| B03 | Besucher lesen Beitragsliste und Einzelbeitrag (LB/EN) | `NewsController`, `NewsRepository`, `templates/news/*` |
| B04 | Startseite: Hero, Porträt, vier Themen, News-Vorschau, Kontaktblock | `HomeController`, `templates/home/index.html.twig` |
| B05 | Kontaktseite: E-Mail-, Standort-, Partei-, Social-Verweise (mailto) | `ContactController`, `templates/contact/index.html.twig` (Formular `ContactType` = toter Code) |
| B06 | Locale-Routing `/lb` `/en` und Sprachumschalter auf jeder Seite | `EventSubscriber/LocaleSubscriber`, Locale-Routen aller Controller, `templates/base.html.twig`, `translations/*` |

## Rückerfassungs-Reihenfolge (Baureihenfolge)

**B01 → B02 → B03 → B06 → B05 → B04**

Nach **Risiko**, nicht nach Nummer — die Rückerfassung ist die Eintrittskarte für
`sdd-qa`, und die QA ist an einem Bestandsprojekt ein Sicherheitsaudit:

1. **B01 Admin-Login** zuerst — trägt die einzige Authentifizierung und Zugriffsregel der
   App. Dringlichster Prüfpunkt: die `AdminFixtures` legen den Admin `mika` mit Passwort
   `admin` an; landet das je in Produktion, ist der Admin-Bereich trivial übernehmbar.
   Außerdem: Rate Limit am Login (aktuell keins erkennbar) und CSRF (vorhanden).
2. **B02 Neuigkeiten verwalten** — nimmt Eingaben entgegen und schreibt in die DB, hinter
   `ROLE_ADMIN`. Zu prüfen: durchgängige Absicherung aller `/admin`-Routen, CSRF auch bei
   Anlage/Bearbeitung, `category`-Freiheit (DB-Enum fehlt).
3. **B03 Neuigkeiten lesen** — öffentliche Eingabe über den `slug`; 404 bei Fehltreffer ist
   vorhanden. Inhalt wird autoescaped (kein `|raw`), also keine offensichtliche Stored-XSS.
4. **B06 Zweisprachigkeit** — Routing-Korrektheit und das Zusammenspiel mit `access_control`;
   sicherheitsnah, aber ohne Personendaten.
5. **B05 Kontaktseite** — geringes Risiko (nur mailto); der tote `ContactType` ist zu
   notieren, nicht zu reparieren.
6. **B04 Startseite** — reine Darstellung, zuletzt.

## QA abgeschlossen — alle Features production-ready (2026-09-10)

Alle sechs Features sind geprüft (funktionale Tests + Angriffsdurchlauf, 40 Tests grün).
**Verdict überall production-ready: ja.** Die blockierenden und mittleren Befunde wurden
über `sdd-build` behoben und erneut geprüft:

| Feature | Status | Behoben | Offen (niedrig) |
|---|---|---|---|
| B01 Admin-Login | approved | Login-Throttling, Fixture-Passwort, Logout-CSRF | — |
| B02 News verwalten | approved | Slug `UniqueEntity` (500 → Feldfehler) | category-DB, Voter, Inline-JS |
| B03 News lesen | approved | Datumsfilter in `show()` (Vorab-Leck) | Paginierung |
| B04 Startseite | review | — (nur niedrig) | `\|raw` auf Übersetzungen |
| B05 Kontaktseite | review | — (nur niedrig) | `ContactType` toter Code |
| B06 Zweisprachigkeit | review | — (nur niedrig) | kein `hreflang` |

B04–B06 bleiben `review` (Bestandsfeatures mit nur niedrigen Befunden; sie sind
production-ready, wurden aber nicht ausgeliefert). Details in den `qa-report.md` und in
`befunde.md`.

**Systemweite Änderung:** neue Abhängigkeit `symfony/rate-limiter` (für Login-Throttling).

**Nächster Schritt:** `/sdd-erfassen abschluss` für den Auditbericht; die niedrigen Befunde
(BF-05…07, 09, 10–12) und Betriebsthemen (Google Fonts, doppelte Asset-Pipeline, `APP_ENV`)
gehören in `sdd-betrieb`.
