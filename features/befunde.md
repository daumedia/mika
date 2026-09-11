# Befunde — projektweit

Stand: 2026-09-10 · Quelle: die `qa-report.md` aller geprüften Features

Diese Liste wird von `sdd-qa` fortgeschrieben, nicht von Hand. Sie ist die Grundlage des
Auditberichts, den `/sdd-erfassen abschluss` daraus baut.

## Offen

| ID | Feature | Befund | Grad | Fundstelle | Seit |
|---|---|---|---|---|---|

— keine offenen Befunde. Die niedrige Aufräum-Runde (BF-05/06/09–12) ist am 2026-09-11
abgeschlossen: BF-05/10/11/12 behoben, BF-06/09 bewusst akzeptiert (siehe unten).

## Behoben

| ID | Feature | Befund | Grad | Behoben am | Ausgeliefert |
|---|---|---|---|---|---|
| BF-01 | B01 | Kein Login-Throttling — Brute Force | hoch | 2026-09-10 | noch nicht (nur behoben, lokal verifiziert) |
| BF-02 | B01 | Dev-Fixture-Admin `mika`/`admin` | hoch | 2026-09-10 | noch nicht (nur behoben) |
| BF-03 | B01 | `/logout` per GET ohne CSRF | niedrig | 2026-09-10 | noch nicht (nur behoben) |
| BF-04 | B02 | Doppelter Slug → HTTP 500 statt Feldfehler | mittel | 2026-09-10 | noch nicht (nur behoben, `UniqueEntity`) |
| BF-08 | B03 | Geplanter Beitrag über Direkt-URL abrufbar | mittel | 2026-09-10 | noch nicht (nur behoben, Datumsfilter in `show()`) |
| BF-13 | 01 / projektweit | `importmap('app')`-Polyfill lud `es-module-shims` von `ga.jspm.io` (Besucher-IP an Dritt-CDN auf jeder Seite) | hoch | 2026-09-11 | **2026-09-11 (deployt)** — `jspm.io` site-weit aus dem Prod-HTML verschwunden |
| BF-07 | B02 | Inline-`<script>` + `onclick`/`onsubmit` im Admin (News-Formular, Dashboard) — verhindert strikte CSP | niedrig | 2026-09-11 | **2026-09-11 (deployt)** — Inline-JS → Stimulus `news-form`/`confirm` |
| BF-14 | projektweit | Lokale Stimulus-Controller wurden von Encore **nicht** gebündelt (`startStimulusApp()` ohne `require.context`); sie liefen nur über die AssetMapper-importmap. Nach BF-13 (importmap entfernt) blieben `nav` (mobiles Menü) & Co. tot — kurz live | mittel | 2026-09-11 | **2026-09-11 (deployt)** — `require.context` im Bootstrap + `core-js`; `nav` im Prod-`app.js` bestätigt |
| BF-05 | B02 | `category` als Magic-String ohne Typsicherheit — unbekannter Wert → fehlende Übersetzung | niedrig | 2026-09-11 | noch nicht (nur behoben, lokal verifiziert) — Backed-Enum `App\Enum\NewsCategory` + `enumType` an der Entität, `EnumType` im Formular; keine Migration nötig (Spaltentyp unverändert, `schema:validate` grün) |
| BF-10 | B04 | `|raw` auf Übersetzungsstrings (latenter XSS-Pfad, aktuell entwicklerkontrolliert) | niedrig | 2026-09-11 | noch nicht (nur behoben, lokal verifiziert) — `<em>`-Markup ins Template geholt, Übersetzungen sind reiner Text; `|raw` projektweit weg |
| BF-11 | B05 | `ContactType` toter Code (nirgends verdrahtet) | niedrig | 2026-09-11 | noch nicht (nur behoben, lokal verifiziert) — `src/Form/ContactType.php` gelöscht |
| BF-12 | B06 | Kein `hreflang` für die Sprachfassungen (SEO) | niedrig | 2026-09-11 | noch nicht (nur behoben, lokal verifiziert) — `hreflang` lb/en/x-default im `<head>` je Seite, `slug` erhalten, `_locale`-Default gefiltert |

## Akzeptiert

Bewusst nicht behoben. Ohne Begründung und Datum ist ein Befund nicht akzeptiert, sondern
vergessen.

| ID | Feature | Befund | Grad | Begründung | Beschlossen am |
|---|---|---|---|---|---|
| BF-06 | B02 | Kein Objekt-Voter — jeder Admin ändert/löscht jeden Beitrag | niedrig | Single-Admin-Betrieb (nur `mika`); ein Objekt-Voter greift erst bei mehreren Redakteuren. Wird nachgezogen, sobald ein zweites Redaktionskonto entsteht. | 2026-09-11 |
| BF-09 | B03 | Keine Paginierung — `findAllPublished()` lädt alle Beiträge | niedrig | Handgepflegter Bestand, einstellige Beitragszahl — kein Performance-Problem absehbar. Nachzuziehen, wenn der Bestand grösser wird (Richtwert ~50 Beiträge). | 2026-09-11 |

## Muster

Was in mehr als einem Feature auftritt — der Grund, warum diese Liste existiert.
Stand nach allen sechs geprüften Features:

- **Fehlende serverseitige Constraints/Validierung an der Datenschicht** — Slug ohne
  `UniqueEntity` (BF-04, **behoben**) und `category` als Magic-String (BF-05, **behoben** —
  Backed-Enum `NewsCategory` + `enumType`). Muster: das Formular war die einzige Schranke.
  Beide korrigiert; die Konvention „Typ/Constraint an der Entität, nicht nur im Formular"
  gilt jetzt projektweit.
- **Sichtbarkeits-/Zugriffsregeln nur an einer Stelle durchgesetzt** — Datumsfilter in der
  Liste, aber nicht in der Detailansicht (BF-08, **behoben**); Admin hängt allein an
  `access_control ^/admin` (B01). Ohne zweite Schicht (kein RLS) rächt sich jede vergessene
  Prüfung sofort — bei BF-08 bestätigt und geschlossen.
- **Härtung des Betriebs** — Login-Throttling fehlte (BF-01, **behoben**), CSP fehlt noch
  (BF-07 Inline-Script), Google Fonts hotlinked (IP-Abfluss, **in sdd-betrieb behoben**:
  lokal), `APP_ENV=dev` committet (**behoben**). Der offene Rest gehört in `sdd-betrieb`.
- **Doppelte Asset-Pipeline (Encore + AssetMapper) — jetzt mit belegtem Schaden.** Der in
  `app-shell.md` notierte Fehlbestand ist nicht mehr nur kosmetisch: Die AssetMapper-Hälfte
  (`importmap('app')`) lädt einen `es-module-shims`-Polyfill von `ga.jspm.io` und überträgt
  die Besucher-IP an ein Dritt-CDN — auf jeder Seite (BF-13, hoch). Erst ein konkretes
  Kriterium (Feature 01, AK-09 „keine externen Dienste") hat den latenten Abfluss sichtbar
  gemacht. Fix: die redundante `importmap('app')`-Zeile entfernen (Encore lädt alles) oder
  `es-module-shims` lokal einbinden.
