# 01 · Rechtstexte (Impressum & Datenschutzerklärung) — Testbericht

Stand: 2026-09-11 (2. Durchlauf, nach Fix) · Geprüft gegen `spec.md` vom 2026-09-11

## Fazit

**Production-ready: ja**

Alle 12 Akzeptanzkriterien sind belegt bestanden. Der im ersten Durchlauf blockierende
Befund **BF-13** (die Datenschutz-Seite lud über den `importmap('app')`-Polyfill
`es-module-shims` von `ga.jspm.io` und übertrug die Besucher-IP an ein Dritt-CDN) ist
behoben — die AssetMapper-Hälfte der doppelten Pipeline wurde entfernt, `jspm.io` ist
projektweit aus dem gerenderten HTML verschwunden, Encore lädt Stimulus/Turbo weiter. Die
zuvor offenen Inhalte sind eingesetzt: Impressum-Postanschrift (OF-01) und Hosting-Anbieter
(OF-02, **Hostinger, Deutschland/EU** — per IP-Geolokalisierung faktisch belegt).

**Ein nicht-technischer Vorbehalt** bleibt und blockiert die technische Abnahme nicht: Die
Rechtstexte sollten vor einer verbindlichen Veröffentlichung von einer **Fachperson/CNPD**
geprüft werden (OF-03) — dieser Skill leistet keine Rechtsberatung. Zusätzlich offen im
Betrieb: der **AV-Vertrag/DPA mit Hostinger** (Region ist bestätigt, das Dokument fehlt).

Nächster Schritt: `/sdd-deploy 01` — löst zugleich den `ga.jspm.io`-Abfluss auf der bereits
live stehenden Seite (B04–B06).

| | Anzahl |
|---|---|
| Akzeptanzkriterien geprüft | 12 von 12 |
| davon bestanden | 12 |
| davon durchgefallen | 0 |
| **nicht prüfbar** | 0 |
| Edge Cases belegt | 3 von 4 (EC-02 nicht auslösbar) |
| Tests neu geschrieben | 14 |
| Tests grün | 54 von 54 |

## Akzeptanzkriterien im Einzelnen

| AK | Ergebnis | Nachweis |
|---|---|---|
| AK-01 | ✅ bestanden | `RechtstexteTest::testAK01_footer_links_both_legal_pages`; Footer auf `/lb` → `/lb/impressum` + `/lb/datenschutz` |
| AK-02 | ✅ bestanden | `testAK02_AK03_AK10_pages_public_and_200` (4 URLs → 200) |
| AK-03 | ✅ bestanden | s. AK-02 (`/lb/datenschutz`, `/en/datenschutz` → 200) |
| AK-04 | ✅ bestanden | `testAK04_locale_matches_url_prefix` (`html[lang]`) |
| AK-05 | ✅ bestanden | `testAK05_language_toggle…` + `…datenschutz_toggle…` (Pille → Gegenfassung) |
| AK-06 | ✅ bestanden | `testAK06_en_shows_authoritative_note` (EN Hinweis, LU nicht) |
| AK-07 | ✅ bestanden | `testAK07_impressum_shows_required_details` — Name/Partei/`mailto`/**Postanschrift** `13, rue de la Fontaine, L-3768 Tétange`; kein Platzhalter; live gerendert |
| AK-08 | ✅ bestanden | `testAK08_datenschutz_names_processing_and_cnpd` — Sentry, CNPD, **Hostinger** |
| AK-09 | ✅ bestanden | Angriff: `jspm/shims`-Zähler auf `/lb`, `/lb/impressum`, `/lb/datenschutz`, `/en/datenschutz` je **0**; keine externen Requests; Inhalt deckt sich mit `docs/datenschutz.md` (Hostinger DE/EU, Sentry EU, lokale Fonts, keine Tracker) |
| AK-10 | ✅ bestanden | 4 URLs 200 ohne Login; außerhalb `^/admin` (Code-Review 1. Durchlauf) |
| AK-11 | ✅ bestanden | `testAK11_pages_have_own_title` |
| AK-12 | ✅ bestanden | Impressum enthält nur die freigegebenen Betreiber-Angaben, keine Dritt-PII |

## Edge Cases

| EC | Ergebnis | Nachweis |
|---|---|---|
| EC-01 | ✅ bestanden | Sprachpille → Gegenfassung, nicht Startseite (`testAK05…`) |
| EC-02 | ⚠️ nicht prüfbar | Fallback-Include korrekt formuliert, aber nicht auslösbar (beide Sprachfassungen existieren) |
| EC-03 | ✅ bestanden | `testEC03…` (`/de/impressum` → 404); Müllpfade → 404 |
| EC-04 | ✅ bestanden | Footer-Links auf öffentlichen Seiten |

## Sicherheitsprüfung

Aktiv angegriffen. Stufe A (Fokus Abschnitte 4 & 6).

| Prüfung | Ergebnis | Beleg |
|---|---|---|
| Zugriff auf fremde ID (IDOR) | ✅ trifft nicht zu | keine parameterbehafteten Routen/Datensätze |
| Zugriffsregeln serverseitig | ✅ bestanden | 4 URLs → 200 ohne Auth; kein DB-Zugriff; außerhalb `^/admin` |
| Rate Limit greift | ✅ trifft nicht zu | statische GET; 10× GET stabil 200 (1. Durchlauf) |
| PII in Logs | ✅ bestanden | `grep var/log` → nur Doctrine-Connect (`password":"<redacted>"`); Legal-Seiten loggen nichts |
| **PII an externe Dienste** | ✅ bestanden | **behoben** — `jspm.io`/`es-module-shims` auf allen Seiten = 0; keine externen Requests mehr (nur Social-Links im Footer) |
| Geheimnisse im Repository | ✅ bestanden | Secret-Scan über `src/ templates/ translations/` → keine Treffer |
| Eingaben (XSS/Injection) | ✅ bestanden | keine Eingabefelder; `?x=<script>` nicht reflektiert; kein `\|raw` |
| Löschen/Konto | ✅ trifft nicht zu | keine Konten/Nutzerdaten |

## Fehler

Keine offenen Fehler.

### BUG-01 · Dritt-CDN-Polyfill `ga.jspm.io` — hoch — **behoben 2026-09-11**
**Betraf:** AK-09. **Behebung:** `importmap('app')` aus `templates/base.html.twig` entfernt
(Encore lädt `app.js` vollständig); der `es-module-shims`-Polyfill von `ga.jspm.io` entfällt
damit projektweit. Verifiziert: `jspm/shims`-Zähler = 0 auf allen Seiten. Ausgeliefert:
noch nicht (wartet auf `/sdd-deploy 01`).

## Neue Tests

| Datei | Fälle | Deckt ab |
|---|---|---|
| `tests/Functional/RechtstexteTest.php` | 14 | AK-01–12, EC-01, EC-03, BF-13 (Wächter gegen Rückkehr des Abflusses) |

## Nächster Schritt

`/sdd-deploy 01`. Vor der Veröffentlichung als *verbindliche* Rechtstexte: Fachprüfung
OF-03 (empfohlen) und AV-Vertrag mit Hostinger ablegen (Betrieb).
