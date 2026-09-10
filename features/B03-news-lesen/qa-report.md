# B03 · Neuigkeiten lesen — Testbericht

Stand: 2026-09-10 · Geprüft gegen `spec.md` (rekonstruiert) vom 2026-09-10
Umgebung: PHP 8.5.10, MySQL 8 (Docker), Test-DB `mika_test_test`, PHPUnit 13

## Fazit

**Production-ready: ja.** Das bei der Erst-Prüfung bestätigte **Vorab-Leck** (BUG-01,
AK-07) ist **behoben und erneut geprüft**: die Einzelansicht filtert jetzt auf
`published_at <= now`, ein geplanter Beitrag liefert per Direkt-URL 404. Liste und
Detailansicht funktionieren zweisprachig, unbekannte Slugs liefern 404, Inhalte werden
escaped ausgegeben. Keine offenen kritischen/hohen/mittleren Befunde → Status → `approved`.

| | Anzahl |
|---|---|
| Akzeptanzkriterien geprüft | 8 von 8 |
| davon bestanden | 7 |
| davon durchgefallen | 1 (AK-07) |
| **nicht prüfbar** | 0 |
| Edge Cases belegt | 1 von 2 |
| Tests neu geschrieben | 8 (1 Datei) |
| Tests grün | 8 von 8 |

## Akzeptanzkriterien im Einzelnen

| AK | Ergebnis | Nachweis |
|---|---|---|
| AK-01 Liste veröffentlicht, absteigend (LB) | ✅ bestanden | `B03NewsLesenTest::testAK01_list_shows_published_desc_lb` (Reihenfolge geprüft) |
| AK-02 Liste englisch | ✅ bestanden | `…::testAK02_list_english_locale` (EN da, LB-Titel nicht) |
| AK-03 Zukünftig datiert nicht in Liste | ✅ bestanden | `…::testAK03_future_dated_absent_from_public_list` |
| AK-04 Leerzustand ohne Beiträge | ✅ bestanden | `…::testAK04_empty_state_when_no_published` (`.news-card` fehlt) |
| AK-05 Detailansicht mit Inhalt | ✅ bestanden | `…::testAK05_detail_shows_content` (Zeilen erhalten) |
| AK-06 Unbekannter Slug → 404 | ✅ bestanden | `…::testAK06_unknown_slug_returns_404` |
| AK-07 Geplanter Beitrag per Direkt-URL (Leck) | ✅ behoben | war ❌ (BUG-01); nach Fix 404 — `…::testAK07_future_dated_not_reachable_via_direct_url` |
| AK-08 Inhalt escaped (kein Stored-XSS) | ✅ bestanden | `…::testAK08_content_is_html_escaped` (roher `<script>` fehlt, `&lt;script&gt;` da) |

## Edge Cases

| EC | Ergebnis | Nachweis |
|---|---|---|
| EC-01 Kategorie außerhalb der vier | ⚠️ nicht prüfbar | Kategorie wird nur über B02-Formular gesetzt; DB-Randfall siehe BF-05 |
| EC-02 Slug in keiner Sprache → 404 | ✅ bestanden | `testAK06…` |

## Sicherheitsprüfung

| Prüfung | Ergebnis | Beleg |
|---|---|---|
| Zugriff auf fremde ID (IDOR) | **BUG-01** | geplanter Beitrag per Slug abrufbar (`testAK07…`) — Inhalts-Disclosure |
| Zugriffsregeln serverseitig | n/a | öffentliche Leseansicht ohne geschützte Objekte (außer dem Datumsfilter-Leck) |
| Eingaben / Stored-XSS | bestanden | `testAK08…` — Inhalt escaped ausgegeben |
| Rate Limit | n/a | öffentlicher, kostenloser Lesezugriff |
| PII in Logs / an Dritte | n/a | keine Personendaten im Feature |
| Geheimnisse im Repository | bestanden | wie B01 — nichts Echtes committet |

## Fehler

### BUG-01 · Geplanter Beitrag über Direkt-URL abrufbar — mittel

**Betrifft:** AK-07, Fehlbestand FB-01
**Reproduktion:**
1. Beitrag mit `publishedAt` in der Zukunft (z. B. 2099) und Slug `geheim-geplant` anlegen.
2. `/lb/news/geheim-geplant` direkt aufrufen (ohne Anmeldung).
**Erwartet:** 404, solange das Datum in der Zukunft liegt (konsistent zur Liste).
**Tatsächlich:** HTTP 200, der Beitrag wird vollständig angezeigt. Beleg:
`testAK07_future_dated_reachable_via_direct_url_BUG`.
**Ort:** `src/Controller/NewsController.php:25` — `findOneBy(['slug' => …])` ohne
Datumsfilter (die Liste nutzt `findAllPublished()` mit Filter).
**Vorschlag:** in `show()` zusätzlich `publishedAt <= now` prüfen (eigene
Repository-Methode) → sonst 404. Behebung in `sdd-build`.
**✔ Behoben 2026-09-10:** neue Methode `NewsRepository::findOnePublishedBySlug()` mit
`publishedAt <= now`; `show()` nutzt sie. Geplanter Beitrag → 404, belegt durch
`testAK07_future_dated_not_reachable_via_direct_url`.
**Hinweis Schwere:** mittel, weil redaktioneller Inhalt betroffen ist (kein Personenbezug)
und der Slug bekannt/erraten sein muss. **Hoch**, falls Beiträge mit Embargo/Sperrfrist
geplant werden — dann ist die Vorab-Sichtbarkeit ein echter Vertrauensbruch.

## Neue Tests

| Datei | Fälle | Deckt ab |
|---|---|---|
| `tests/Functional/B03NewsLesenTest.php` | 8 | AK-01…AK-06, AK-08, AK-07 (BUG-01) |

## Nächster Schritt

BUG-01 behoben und erneut geprüft (Suite 40/40 grün). Keine offenen kritischen/hohen/
mittleren Befunde → **Status B03 → `approved`**. Niedrige Punkte (BF-09) bleiben in
`features/befunde.md`.
