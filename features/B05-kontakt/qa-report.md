# B05 · Kontaktseite — Testbericht

Stand: 2026-09-10 · Geprüft gegen `spec.md` (rekonstruiert) · Umgebung: PHP 8.5, MySQL 8, PHPUnit 13

## Fazit

**Production-ready: ja** — keine kritischen/hohen Befunde. Die Seite bietet bewusst nur
`mailto`-/Social-Links, **kein Formular** — das im Test bestätigt (kein `<form>`, kein
`input`/`textarea`). `ContactType` bleibt toter Code (niedrig). Status `review`.

| | Anzahl |
|---|---|
| Akzeptanzkriterien geprüft | 4 von 4 |
| bestanden | 4 · durchgefallen | 0 · nicht prüfbar | 0 |
| Tests neu | 3 · grün | 3 von 3 |

## Akzeptanzkriterien

| AK | Ergebnis | Nachweis |
|---|---|---|
| AK-01 Karten + kein Formular (LB) | ✅ | `B05KontaktTest::testAK01_lb_shows_contact_links_and_no_form` |
| AK-02 Englisch | ✅ | `…::testAK02_en_locale` |
| AK-03 mailto | ✅ | `…::testAK01…` (`a[href^="mailto:"]`) |
| AK-04 externe Links sicher (`_blank`,`noopener`) | ✅ | `…::testAK03_AK04_party_and_social_links_are_safe_external` |

## Sicherheitsprüfung

| Prüfung | Ergebnis | Beleg |
|---|---|---|
| Eingaben | n/a | kein Eingabekanal (bestätigt: kein Formular) |
| externe Links (Tabnabbing) | bestanden | `rel="noopener noreferrer"` auf `_blank`-Links |
| PII / Geheimnisse | bestanden | keine Verarbeitung; E-Mail bewusst als `mailto` sichtbar |

## Fehler

Keine kritischen/hohen. Niedrig: **BF-11** (`ContactType` toter Code — nirgends
verdrahtet; entfernen oder als eigenes Feature Stufe B). E-Mail im Klartext (akzeptiert,
kosmetisch).

## Neue Tests

| Datei | Fälle |
|---|---|
| `tests/Functional/B05KontaktTest.php` | 3 |

## Nächster Schritt

Keine Reparatur nötig (production-ready). BF-11 in `befunde.md`. Status `review`.
