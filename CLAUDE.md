# CLAUDE.md — Mika Ferreira

Zweisprachige (Luxemburgisch/Englisch) Personen- und Wahlkampf-Website von Mika (Michael)
Ferreira, DP Kayl-Téiteng. Öffentliche Präsenzseite mit News-System und geschütztem
Admin-Bereich zum Pflegen der Neuigkeiten.

## SDD-Artefakte

**Artefaktpfad: `docs/`** — die `sdd-`-Skills lesen und schreiben ihre Artefakte hier.

- `docs/prd.md` — Produktvision, Scope, Rahmenbedingungen, Datenschutz (Stufe A)
- `docs/datenmodell.md` — Entitäten `News` und `Admin`
- `docs/design-system.md` — Farben, Typografie, Komponenten
- `docs/app-shell.md` — Navigation, Layout, Routing, Asset-Pipeline
- `features/index.md` — Feature-Inventar (Bestand, IDs `B01`–`B06`) und Baureihenfolge

Das Projekt wurde rückwirkend erfasst (`sdd-erfassen`, Phase 1, 2026-09-10). Die Features
stehen auf `bestand`; Rückerfassung (Spec + Design) läuft je Feature über
`/sdd-erfassen B01` in der Reihenfolge B01 → B02 → B03 → B06 → B05 → B04.

## Stack

Symfony 8.0 (PHP ≥ 8.4) · Doctrine ORM 3 · MySQL 8 (Docker) · Twig · Tailwind CSS v4 ·
Stimulus + Turbo (Symfony UX). Stack-Profil: `symfony-doctrine`.

Hinweis: Die Asset-Pipeline ist derzeit **doppelt** aufgesetzt (Webpack Encore *und*
AssetMapper/importmap) — siehe `docs/app-shell.md`, Fehlbestand.

## Befehle (Makefile)

```bash
make init        # Docker + Dependencies + DB + Fixtures (Erstsetup)
make start       # Docker, Symfony-Server, Asset-Watcher
make stop        # alles stoppen
make db          # Migrationen ausführen
make migration   # Migration aus Entity-Änderungen erzeugen
make db-reset    # ⚠️ DB löschen, migrieren, Fixtures laden
make assets      # Assets für Produktion bauen (npm run build)
make cc          # Cache leeren
make fix         # PHP CS Fixer
php bin/phpunit  # Tests

# Admin anlegen (produktionssicher, statt der Dev-Fixture):
php bin/console app:create-admin   # siehe src/Command/CreateAdminCommand.php
```

## Vor jedem Deploy beachten

- `.env` steht auf `APP_ENV=dev` — für Produktion `prod` und `APP_DEBUG=0` setzen.
- **Nie** die Dev-Fixtures (`AdminFixtures`, Passwort `admin`) in Produktion laden.
- Migration vor Code (siehe Stack-Profil).
