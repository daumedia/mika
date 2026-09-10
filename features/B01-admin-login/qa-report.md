# B01 · Admin-Login — Testbericht

Stand: 2026-09-10 · Geprüft gegen `spec.md` (rekonstruiert) vom 2026-09-10
Umgebung: PHP 8.5.10, MySQL 8 (Docker), Test-DB `mika_test_test`, PHPUnit 13

## Fazit

**Production-ready: ja** (Stand 2026-09-10 nach Behebung — Erst-Prüfung war „nein")

Alle Akzeptanzkriterien sind bestanden und durch ausgeführte Tests belegt. Die beiden
hohen Befunde der Erst-Prüfung wurden über `sdd-build` behoben und **erneut geprüft**:
**Login-Throttling** greift jetzt (der 6. Versuch wird auch mit korrektem Passwort
geblockt), und der **Dev-Fixture-Admin** hat kein hartkodiertes Passwort mehr. Auch der
niedrige Befund (GET-Logout ohne CSRF) ist behoben — der Logout trägt jetzt ein CSRF-Token.
Keine offenen kritischen/hohen Befunde mehr.

> **Erst-Prüfung (2026-09-10, vor Behebung):** Production-ready **nein** — BUG-01 (kein
> Throttling, hoch) und BUG-02 (Fixture `mika`/`admin`, hoch). Historie erhalten.

| | Anzahl |
|---|---|
| Akzeptanzkriterien geprüft | 7 von 7 |
| davon bestanden | 7 |
| davon durchgefallen | 0 |
| **nicht prüfbar** | 0 |
| Edge Cases belegt | 2 von 2 |
| Tests neu geschrieben | 11 (1 Datei) |
| Tests grün | 11 von 11 |

## Akzeptanzkriterien im Einzelnen

| AK | Ergebnis | Nachweis |
|---|---|---|
| AK-01 Login-Erfolg → `/admin` | ✅ bestanden | `B01AdminLoginTest::testAK01_login_success_redirects_to_dashboard` |
| AK-02 Falsches Passwort → Fehler + Name bleibt | ✅ bestanden | `…::testAK02_login_failure_shows_error_and_keeps_username` (prüft `.text-red-800` + `input#username[value=…]`) |
| AK-03 Bereits angemeldet → `/admin` | ✅ bestanden | `…::testAK03_already_authenticated_login_redirects_to_dashboard` |
| AK-04 `/admin` ohne Anmeldung → `/login` | ✅ bestanden | `…::testAK04_admin_requires_authentication` (302, Location enthält `/login`) |
| AK-05 Logout → Sitzung endet, `/admin` sperrt wieder | ✅ bestanden | `…::testAK05_logout_ends_session_and_admin_locks_again` |
| AK-06 Login ohne gültiges CSRF → abgelehnt | ✅ bestanden | `…::testAK06_login_without_valid_csrf_is_rejected` (Location `/login`, nicht `/admin`) |
| AK-07 Passwort nur als Hash | ✅ bestanden | `dbal:run-sql` → `algo_prefix=$2y$`, `len=60` (bcrypt), kein Klartext |

## Edge Cases

| EC | Ergebnis | Nachweis |
|---|---|---|
| EC-01 leeres Feld | ✅ bestanden | Auth scheitert; von AK-02/Malicious-Tests mit abgedeckt |
| EC-02 Logout per GET | ✅ bestanden | `testAK05…` löst Logout per GET aus (302) — zugleich Beleg für BUG-03 |

## Sicherheitsprüfung

Aktiv angegriffen (`~/.claude/sdd/sicherheit.md`, Stufe A → Schwerpunkt §3/§4/§6).

| Prüfung | Ergebnis | Beleg |
|---|---|---|
| Zugriff auf fremde ID (IDOR) | n/a | B01 hat keinen Objektzugriff; Zugriffssperre siehe AK-04 |
| Zugriffsregeln serverseitig | bestanden | `access_control ^/admin` erzwingt Redirect (testAK04/06), kein Frontend-Trick |
| Rate Limit greift | **BUG-01** | `testFB01_no_login_throttling…`: 6 Fehlversuche, alle 302, kein 429 |
| PII in Logs | bestanden | `var/log/dev.log`: DB-Passwort als `<redacted>`, keine Klartext-Passwörter/Tokens |
| PII an externe Dienste | n/a | Login sendet nichts an Dritte |
| Geheimnisse im Repository | bestanden | `.env.local` nicht getrackt; getracktes `.env` hat leeres `APP_SECRET` + nur lokale Platzhalter; keine Live-Keys in der Historie |
| Eingaben (SQLi/XSS/Traversal/überlang) | bestanden | `testAttack_malicious_login_input…` (4 Datensätze) → jeweils 302, keine 500; `admin`-Tabelle nach SQLi intakt (`COUNT=1`) |

## Fehler

### BUG-01 · Kein Login-Throttling — hoch

**Betrifft:** Sicherheitskatalog §4 (Fehlbestand FB-01)
**Reproduktion:** 6× `/login` mit korrektem Benutzernamen und falschem Passwort abschicken.
**Erwartet:** ab dem sechsten Versuch eine Sperre/Verzögerung (z. B. HTTP 429 oder
Throttling-Meldung).
**Tatsächlich:** alle sechs Versuche liefern 302 → `/login` (normale „ungültige
Zugangsdaten"), keine Sperre. Beleg: `testFB01_no_login_throttling_after_repeated_failures`.
**Ort:** `config/packages/security.yaml` (kein `login_throttling` unter Firewall `main`).
**Vorschlag:** `login_throttling: max_attempts: 5` unter der Firewall — Behebung in `sdd-build`.
**✔ Behoben 2026-09-10:** `login_throttling: {max_attempts: 5, interval: '15 minutes'}` in
`security.yaml`; `symfony/rate-limiter` installiert. Reproduktion greift nicht mehr —
`testAK_login_throttling_blocks_after_five_failures` belegt, dass der 6. Versuch auch mit
korrektem Passwort geblockt wird.

### BUG-02 · Dev-Fixture-Admin `mika` / Passwort `admin` — hoch

**Betrifft:** Sicherheitskatalog §6 (Fehlbestand FB-02)
**Reproduktion:** `make db-reset` bzw. `doctrine:fixtures:load` ausführen → Admin `mika`
mit Passwort `admin` existiert und meldet sich erfolgreich an.
**Erwartet:** kein rate- bzw. produktionsfähiges Standardpasswort.
**Tatsächlich:** triviales Passwort im Klartext in der Fixture. `require-dev` schützt nur,
solange in Produktion `--no-dev` gilt **und** die Fixtures nie geladen werden — wird das
Passwort real übernommen, ist der Admin-Bereich sofort offen. **Wird kritisch, sobald in
Produktion erreichbar.**
**Ort:** `src/DataFixtures/AdminFixtures.php:22`
**Vorschlag:** Fixture-Passwort randomisieren/aus ENV lesen; Produktionsadmin nur über
`app:create-admin`.
**✔ Behoben 2026-09-10:** `AdminFixtures` liest das Passwort aus `ADMIN_PASSWORD` oder
erzeugt ein zufälliges (16 Hex-Zeichen) und gibt es einmalig aus. Kein hartkodiertes
`admin` mehr.

### BUG-03 · `/logout` per GET ohne CSRF — niedrig

**Betrifft:** Fehlbestand FB-03
**Reproduktion:** `GET /logout` als angemeldeter Nutzer (auch fremd ausgelöst, z. B. per
`<img src>`), meldet ab.
**Erwartet:** Logout gegen CSRF geschützt (Token/POST).
**Tatsächlich:** GET ohne Token meldet ab (`testAK05…` belegt den GET-Logout).
**Ort:** `config/packages/security.yaml` (`logout:` ohne `csrf_parameter`).
**Vorschlag:** Logout-CSRF aktivieren — geringe Schwere.
**✔ Behoben 2026-09-10:** `logout: enable_csrf: true`; der Logout-Link im Dashboard nutzt
jetzt `logout_path('main')` (trägt das Token). Tokenloses `GET /logout` meldet nicht mehr
ab (im Test bestätigt).

## Neue Tests

| Datei | Fälle | Deckt ab |
|---|---|---|
| `tests/Functional/B01AdminLoginTest.php` | 11 | AK-01…AK-06, Eingabe-Angriff (4), FB-01 |

## Systemweite Änderungen (durch die Behebung)

- **Neue Abhängigkeit `symfony/rate-limiter`** (v8.0.14) — nötig für `login_throttling`.
  In `composer.json`/`composer.lock` ergänzt.
- `config/packages/security.yaml`: `login_throttling` + `logout.enable_csrf` ergänzt.
- `templates/admin/dashboard.html.twig`: Logout-Link auf `logout_path('main')` umgestellt.
- `src/DataFixtures/AdminFixtures.php`: Fixture-Passwort nicht mehr hartkodiert.

## Nächster Schritt

BUG-01…03 behoben und erneut geprüft (11 Tests grün, gesamte Suite 29/29). Keine offenen
kritischen/hohen Befunde → **Status B01 → `approved`** (production-ready). Ein Deployment
erfolgt hier nicht (der Code läuft; Auslieferung wäre `/sdd-deploy B01`).
