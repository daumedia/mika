# B04 · Startseite — Testbericht

Stand: 2026-09-10 · Geprüft gegen `spec.md` (rekonstruiert) · Umgebung: PHP 8.5, MySQL 8, PHPUnit 13

## Fazit

**Production-ready: ja** — keine kritischen/hohen Befunde. Nur niedrige Punkte
(`|raw` auf entwicklerkontrollierten Übersetzungen, Porträt ohne Fallback). Status bleibt
`review` (Bestandsfeature mit niedrigen Befunden).

| | Anzahl |
|---|---|
| Akzeptanzkriterien geprüft | 5 von 6 (AK-06 Google Fonts querschnittlich) |
| bestanden | 5 · durchgefallen | 0 · nicht prüfbar | 1 |
| Tests neu | 4 · grün | 4 von 4 |

## Akzeptanzkriterien

| AK | Ergebnis | Nachweis |
|---|---|---|
| AK-01 Alle Abschnitte (LB) | ✅ | `B04StartseiteTest::testAK01_home_has_all_sections` (#about/#themes/#contact) |
| AK-02 Englisch | ✅ | `…::testAK02_english_locale` |
| AK-03 Vorschau bei leer ausgeblendet | ✅ | `…::testAK03_news_preview_hidden_when_empty` (kein `.news-card`) |
| AK-03/04 Vorschau zeigt + verlinkt | ✅ | `…::testAK03_AK04_news_preview_shows_published` (`/lb/news/vorschau`) |
| AK-05 Anker-Scroll | ⚠️ nicht prüfbar | reines CSS `scroll-behavior`; kein Verhalten für den Test |
| AK-06 Google Fonts / IP | ⚠️ Hinweis | querschnittlich, siehe PRD OF-03 / BF-14 |

## Sicherheitsprüfung

| Prüfung | Ergebnis | Beleg |
|---|---|---|
| Eingaben / XSS | n/a | keine Eingaben; `|raw` nur auf Übersetzungen (BF-10) |
| Zugriff / IDOR / Rate Limit | n/a | öffentliche Darstellung |
| PII / Geheimnisse | bestanden | wie B01 — nichts Sensibles |

## Fehler

Keine kritischen/hohen. Niedrig: **BF-10** (`|raw` auf `home.hero.tagline`/`home.about.headline`
— aktuell entwicklerkontrolliert, kein aktueller XSS; Risiko, falls Übersetzungspflege
delegiert wird). Porträt ohne Fallback (kosmetisch).

## Neue Tests

| Datei | Fälle |
|---|---|
| `tests/Functional/B04StartseiteTest.php` | 4 |

## Nächster Schritt

Keine Reparatur nötig (production-ready). BF-10 in `befunde.md`. Status `review`.
