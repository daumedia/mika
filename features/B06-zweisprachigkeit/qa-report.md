# B06 · Zweisprachigkeit — Testbericht

Stand: 2026-09-10 · Geprüft gegen `spec.md` (rekonstruiert) · Umgebung: PHP 8.5, MySQL 8, PHPUnit 13

## Fazit

**Production-ready: ja** — keine kritischen/hohen Befunde. Locale-Routing, der 301-Redirect
und die slug-erhaltende Sprachumschaltung sind belegt. Niedrig: fehlendes `hreflang`.
Status `review`.

| | Anzahl |
|---|---|
| Akzeptanzkriterien geprüft | 5 von 6 (AK-05 über AK-04 mit abgedeckt) |
| bestanden | 5 · durchgefallen | 0 · nicht prüfbar | 0 |
| Tests neu | 4 · grün | 4 von 4 |

## Akzeptanzkriterien

| AK | Ergebnis | Nachweis |
|---|---|---|
| AK-01/04 `/lb` `/en` mit korrektem `lang` | ✅ | `B06ZweisprachigkeitTest::testAK01_AK04_lb_and_en_pages_have_correct_lang` |
| AK-02 Umschalter erhält Slug | ✅ | `…::testAK02_toggle_preserves_slug_across_locales` (`/en/news/mein-slug`) |
| AK-03 `/` → 301 `/lb` | ✅ | `…::testAK03_root_redirects_301_to_lb` |
| AK-05 Standard `lb` | ✅ | via AK-01 (LocaleSubscriber default) |
| AK-06 Admin nicht locale-präfixiert | ✅ | `…::testAK06_admin_area_is_not_locale_prefixed` |

## Sicherheitsprüfung

| Prüfung | Ergebnis | Beleg |
|---|---|---|
| Routing / offene Endpunkte | bestanden | `/admin` von einer Regel gedeckt (302→/login); öffentliche Routen wie erwartet |
| Eingaben / PII / Geheimnisse | n/a | reine Routing-/Darstellungslogik |

## Fehler

Keine kritischen/hohen. Niedrig: **BF-12** (kein `hreflang` für die Sprachfassungen —
SEO). `route_map` doppelt im Template gepflegt (Wartungsrisiko, kosmetisch).

## Neue Tests

| Datei | Fälle |
|---|---|
| `tests/Functional/B06ZweisprachigkeitTest.php` | 4 |

## Nächster Schritt

Keine Reparatur nötig (production-ready). BF-12 in `befunde.md`. Status `review`.
