# B03 · Neuigkeiten lesen — Systemdesign

Status: `rekonstruiert` · Stand: 2026-09-10 · Stack-Profil: `symfony-doctrine`

**Kein Code in diesem Dokument.**

## Überblick

Zwei öffentliche Ansichten der `News`-Entity: eine Liste (nur veröffentlichte, absteigend)
und eine Einzelansicht per Slug. Die Sprache steuert, welche Spalten (LB/EN) gezeigt
werden. Die Liste filtert auf `published_at <= now`; die Einzelansicht filtert **nicht**
(Fehlbestand FB-01).

## Seiten und Routen

| Route | Zweck | Zugang |
|---|---|---|
| `GET /lb/news`, `GET /en/news` | Beitragsliste | öffentlich |
| `GET /lb/news/{slug}`, `GET /en/news/{slug}` | Einzelbeitrag | öffentlich |

## Komponentenstruktur

```
NewsController
├── index()  findAllPublished() → Liste / Leerzustand
└── show()   findOneBy(slug) → 404 bei Fehltreffer
NewsRepository
├── findAllPublished()  published_at <= now, DESC
└── findLatest(limit)   published_at <= now, DESC, LIMIT   (von B04 genutzt)
templates/news/
├── index.html.twig  Hero · Karten (Datum, Kategorie, Titel, Kurzfassung) · Leerzustand
└── show.html.twig    Hero (Titel) · Karte (Kurzfassung, Fließtext pre-line)
```

## Datenmodell

Liest `news` (siehe `docs/datenmodell.md`), schreibt nicht. Zugriff über `slug` (Unique),
Sortierung über `published_at`.

## Zugriffsregeln

| Wer | Darf lesen | Darf schreiben | Erzwungen durch |
|---|---|---|---|
| alle (anonym) | veröffentlichte Beiträge (Liste) | — | Repository-Filter `published_at <= now` |
| alle (anonym) | jeder Beitrag per Slug (auch geplant) | — | **kein Filter** in `show()` — FB-01 |

## Missbrauchsschutz

| Endpunkt | Limit | Verhalten | Wo |
|---|---|---|---|
| `/news`, `/news/{slug}` | keins | öffentlich, kostenlos → trifft nicht zu | — |

## Externe Dienste

Keine (Google Fonts kommen aus der App-Shell, siehe B06/`app-shell.md`).

## Erkennbare Entscheidungen

| # | Entscheidung | Alternative | Warum so (soweit erkennbar) |
|---|---|---|---|
| 1 | Slug-basierte URLs, in beiden Sprachen gleich | id-basierte oder je Sprache eigener Slug | stabile, teilbare URL |
| 2 | Liste filtert Datum, Einzelansicht nicht | beide filtern | **Grund nicht erkennbar** — wirkt wie ein Versehen (FB-01) |
| 3 | `whitespace-pre-line` statt Markdown | Rich-Text-Editor | einfache Absätze genügen |

## Abdeckung der Akzeptanzkriterien

| AK | Erfüllt durch | Anmerkung |
|---|---|---|
| AK-01 | `index()` + `findAllPublished()` + `index.html.twig` | |
| AK-02 | dieselbe Ansicht, Locale = en | |
| AK-03 | Repository-Filter `published_at <= now` | |
| AK-04 | Leerzustand-Zweig in `index.html.twig` | |
| AK-05 | `show()` + `show.html.twig` | |
| AK-06 | `createNotFoundException()` bei leerem Treffer | |
| AK-07 ⚠ | `show()` ohne Datumsfilter | als Fehler eingestuft → FB-01 |
| AK-08 | Twig-Autoescaping (kein `|raw`) | |
