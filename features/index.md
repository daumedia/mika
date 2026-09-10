# Features

Stand: 2026-09-10 · Stack-Profil: `symfony-doctrine` · Artefaktpfad: `docs/`

Bestandsinventar, rückwärts aus dem Code erfasst (`sdd-erfassen`, Phase 1). Alle Features
sind `bestand` — gebaut, bevor die Kette da war, noch ohne `spec.md`/`design.md`. IDs mit
`B`-Präfix. Rückerfassung erfolgt Feature für Feature in eigenen Sessions über
`/sdd-erfassen B01`.

| ID | Feature | Prio | Status | Abhängig von | Zuletzt |
|---|---|---|---|---|---|
| B01 | Admin-Login | P0 | bestand | — | 2026-09-10 |
| B02 | Neuigkeiten verwalten | P0 | bestand | B01 | 2026-09-10 |
| B03 | Neuigkeiten lesen | P0 | bestand | B02 | 2026-09-10 |
| B04 | Startseite | P0 | bestand | B03, B06 | 2026-09-10 |
| B05 | Kontaktseite | P1 | bestand | B06 | 2026-09-10 |
| B06 | Zweisprachigkeit (LB/EN) | P0 | bestand | — | 2026-09-10 |

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

## Hinweis zum Umfang

Sechs Features = sechs Rückerfassungs-Sessions. Danach `/sdd-erfassen abschluss` für den
Auditbericht. Kein Feature wird hier verändert — Befunde werden notiert und erst nach der
QA über den Fehlerauftrag-Eingang von `sdd-build` behoben.

**Nächster Schritt:** `/sdd-erfassen B01`
