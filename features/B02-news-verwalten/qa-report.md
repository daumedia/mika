# B02 · Neuigkeiten verwalten — Testbericht

Stand: 2026-09-10 · Geprüft gegen `spec.md` (rekonstruiert) vom 2026-09-10
Umgebung: PHP 8.5.10, MySQL 8 (Docker), Test-DB `mika_test_test`, PHPUnit 13

## Fazit

**Production-ready: ja.** Der einzige mittlere Fehler (BUG-01, Slug-Duplikat → 500) ist
**behoben und erneut geprüft**; es bleibt nur ein **nicht prüfbares** Kriterium (AK-07,
clientseitiges JS). Status → `approved`.

Der CRUD-Weg funktioniert und ist durch ausgeführte Tests belegt: Anlegen, Bearbeiten,
Löschen (mit CSRF), Pflichtfeld-Validierung, 404 bei unbekannter ID und die Sperre für
nicht angemeldete Zugriffe. Der einzige echte Fehler ist ein **500 bei doppeltem Slug**
(fehlendes `UniqueEntity`) — ärgerlich, aber kein Zugriffs- oder Datenleck. Die
clientseitige Slug-Automatik (AK-07) ließ sich mit dem BrowserKit-Client nicht ausführen.
Status bleibt `review`; die Befunde stehen in `befunde.md`, es geht mit B03 weiter.

| | Anzahl |
|---|---|
| Akzeptanzkriterien geprüft | 9 von 10 (AK-07 nicht prüfbar) |
| davon bestanden | 9 |
| davon durchgefallen | 0 |
| **nicht prüfbar** | 1 (AK-07) |
| Edge Cases belegt | 1 von 2 (EC-01 via BUG-01) |
| Tests neu geschrieben | 10 (1 Datei) |
| Tests grün | 10 von 10 |

## Akzeptanzkriterien im Einzelnen

| AK | Ergebnis | Nachweis |
|---|---|---|
| AK-01 Dashboard listet alle (inkl. zukünftig) | ✅ bestanden | `B02NewsVerwaltenTest::testAK01_dashboard_lists_all_including_future_dated` |
| AK-02 Anlegen speichert + Flash | ✅ bestanden | `…::testAK02_create_saves_and_shows_flash` |
| AK-03 Pflichtfeld leer → nicht gespeichert | ✅ bestanden | `…::testAK03_missing_required_field_does_not_save` (HTTP 422, Anzahl unverändert) |
| AK-04 Bearbeiten persistiert | ✅ bestanden | `…::testAK04_edit_persists_change` |
| AK-05 Löschen mit CSRF entfernt | ✅ bestanden | `…::testAK05_delete_with_valid_csrf_removes` (Anzahl → 0) |
| AK-06 Unbekannte ID → 404 | ✅ bestanden | `…::testAK06_unknown_id_returns_404` |
| AK-07 Slug-Autogenerierung (Client-JS) | ⚠️ nicht prüfbar | clientseitiges JS; BrowserKit führt kein JS aus — bräuchte Browser-Test (Panther) |
| AK-08 Unauth. `/admin/news/*` → `/login` | ✅ bestanden | `…::testAK08_unauthenticated_admin_route_redirects_to_login` |
| AK-09 Löschen ohne CSRF → nicht entfernt | ✅ bestanden | `…::testAK09_delete_without_valid_csrf_does_not_remove` (Anzahl bleibt 1) |
| AK-10 Anlegen ohne Form-CSRF → abgelehnt | ✅ bestanden | `…::testAK10_create_without_valid_form_csrf_is_rejected` (HTTP 422, nichts gespeichert) |

## Edge Cases

| EC | Ergebnis | Nachweis |
|---|---|---|
| EC-01 Kategorie außerhalb der vier | ⚠️ nicht prüfbar | über das Formular nicht wählbar; DB-Ebene ungeprüft (siehe BF-05) |
| EC-02 zukünftiges `publishedAt` erlaubt | ✅ bestanden | `testAK01…` legt Beitrag mit Datum 2099 an, erscheint im Dashboard |

## Sicherheitsprüfung

| Prüfung | Ergebnis | Beleg |
|---|---|---|
| Zugriff auf fremde ID (IDOR) | n/a | nur eine Rolle (`ROLE_ADMIN`); kein Mehrbenutzer-Objektzugriff (strukturell BF-06) |
| Zugriffsregeln serverseitig | bestanden | `testAK08…` — `access_control ^/admin` sperrt anonym |
| CSRF (Löschen / Formular) | bestanden | `testAK09…` (manuelles Token) + `testAK10…` (Form-CSRF) |
| Eingaben | bestanden/BUG-01 | Pflichtfeld-Validierung greift (AK-03); doppelter Slug → 500 (BUG-01) |
| PII in Logs | bestanden | wie B01 — keine Klartext-Personendaten in `var/log` |
| Geheimnisse im Repository | bestanden | wie B01 — nichts Echtes committet |

## Fehler

### BUG-01 · Doppelter Slug führt zu HTTP 500 statt Feldfehler — mittel

**Betrifft:** AK-02 / EC-01, Fehlbestand FB-01
**Reproduktion:**
1. Beitrag mit Slug `dup` anlegen.
2. Zweiten Beitrag mit demselben Slug `dup` anlegen.
**Erwartet:** Feldfehler am Slug („bereits vergeben"), Formular bleibt stehen.
**Tatsächlich:** Doctrine `UniqueConstraintViolation` → **HTTP 500**. Beleg:
`testBUG_duplicate_slug_causes_500`.
**Ort:** `src/Form/NewsType.php:58` (nur `NotBlank`), `src/Entity/News.php:38` (Unique-Index)
**Vorschlag:** `#[UniqueEntity('slug')]` auf die Entität — Behebung in `sdd-build`.
**✔ Behoben 2026-09-10:** `#[UniqueEntity(fields: ['slug'])]` auf `News`. Doppelter Slug
liefert jetzt HTTP 422 (Feldfehler), kein 500 — `testAK_duplicate_slug_is_field_error_not_500`.

## Neue Tests

| Datei | Fälle | Deckt ab |
|---|---|---|
| `tests/Functional/B02NewsVerwaltenTest.php` | 10 | AK-01…AK-06, AK-08…AK-10, BUG-01 |

## Nächster Schritt

BUG-01 behoben und erneut geprüft (Suite 40/40 grün). Keine offenen kritischen/hohen/
mittleren Fehler → **Status B02 → `approved`**. Die niedrigen strukturellen Punkte
(BF-05…07) bleiben in `features/befunde.md`.
