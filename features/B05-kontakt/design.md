# B05 · Kontaktseite — Systemdesign

Status: `rekonstruiert` · Stand: 2026-09-10 · Stack-Profil: `symfony-doctrine`

**Kein Code in diesem Dokument.**

## Überblick

Eine rein statische Kontaktseite je Sprache. Der Controller rendert nur ein Template mit
drei Info-Karten (E-Mail, Standort, Partei) und einer Social-Media-Zeile. Keine Eingabe,
keine Verarbeitung, keine Persistenz.

## Seiten und Routen

| Route | Zweck | Zugang |
|---|---|---|
| `GET /lb/contact`, `GET /en/contact` | Kontaktwege anzeigen | öffentlich |

## Komponentenstruktur

```
ContactController::index()   rendert nur contact/index.html.twig
templates/contact/index.html.twig
├── Hero (Überschrift, Beschreibung)
├── Karten: E-Mail (mailto) · Standort (Luxembourg) · Partei (dp.lu)
└── Social-Icons: Facebook · Instagram · LinkedIn
[ungenutzt] Form/ContactType   Name, E-Mail, Betreff, Nachricht  → toter Code (FB-01)
```

## Datenmodell

Keins.

## Zugriffsregeln

| Wer | Darf lesen | Darf schreiben | Erzwungen durch |
|---|---|---|---|
| alle | die Seite | — | öffentliche Route |

## Missbrauchsschutz

Nicht anwendbar — kein Eingabekanal.

## Externe Dienste

Keine, die Daten empfangen — nur ausgehende Links (`dp.lu`, Social-Profile, `mailto`).

## Erkennbare Entscheidungen

| # | Entscheidung | Alternative | Warum so (soweit erkennbar) |
|---|---|---|---|
| 1 | mailto/Links statt Formular | Kontaktformular mit Mailversand | kein Spam-/DSGVO-Aufwand — Stufe A bleibt |
| 2 | `ContactType` im Repo belassen | entfernen | **Grund nicht erkennbar** — wirkt wie ein nie fertiggestellter Rest (FB-01) |

## Abdeckung der Akzeptanzkriterien

| AK | Erfüllt durch | Anmerkung |
|---|---|---|
| AK-01 | `contact/index.html.twig` Karten + Social | kein Formular |
| AK-02 | dieselbe Ansicht, Locale = en | |
| AK-03 | `mailto:`-Link auf der E-Mail-Karte | |
| AK-04 | `target="_blank" rel="noopener noreferrer"` | |

*(Hinweis: `Form/ContactType` deckt kein AK ab — Zeichen für toten Code, FB-01.)*
