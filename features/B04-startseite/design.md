# B04 · Startseite — Systemdesign

Status: `rekonstruiert` · Stand: 2026-09-10 · Stack-Profil: `symfony-doctrine`

**Kein Code in diesem Dokument.**

## Überblick

Eine Controller-Aktion je Sprache rendert eine mehrteilige Scroll-Seite und übergibt die
drei neuesten Beiträge (`findLatest(3)`) an die News-Vorschau. Alle Texte kommen aus den
Übersetzungsdateien; zwei Strings werden mit `|raw` ausgegeben. Das Porträt liegt statisch
unter `public/images/mika.jpg`.

## Seiten und Routen

| Route | Zweck | Zugang |
|---|---|---|
| `GET /lb`, `GET /en` | Startseite je Sprache | öffentlich |
| `GET /` | 301 → `/lb` (siehe B06) | öffentlich |

## Komponentenstruktur

```
HomeController::index()   findLatest(3) → home/index.html.twig
templates/home/index.html.twig
├── Hero        Badge · Name · Tagline(|raw) · CTAs(#themes,#contact) · Porträt
├── About       Kurzporträt p1–p3 · Inklusions-Karte · drei Werte
├── Themes      vier Karten: inclusion · youth · housing · culture
├── News-Vorschau  bis zu 3 Karten (oder ausgeblendet) → Detailseite
└── Kontaktblock   E-Mail-/Standort-Karten · Social-Icons
```

## Datenmodell

Liest `news` über `findLatest(3)` (nur `published_at <= now`, DESC, LIMIT 3). Schreibt
nicht.

## Zugriffsregeln

| Wer | Darf lesen | Darf schreiben | Erzwungen durch |
|---|---|---|---|
| alle | die Startseite + Vorschau veröffentlichter Beiträge | — | öffentliche Route + Repository-Filter |

## Missbrauchsschutz

Nicht anwendbar (Darstellung, keine Eingaben).

## Externe Dienste

| Dienst | Wofür | Was geht hin | Was wird vorher entfernt |
|---|---|---|---|
| Google Fonts | Fraunces/Figtree | Besucher-IP (beim Laden) | — (nichts; Fehlbestand — lokal ausliefern, PRD OF-03) |

## Erkennbare Entscheidungen

| # | Entscheidung | Alternative | Warum so (soweit erkennbar) |
|---|---|---|---|
| 1 | Scroll-Seite mit Abschnitten | getrennte Unterseiten je Abschnitt | Landingpage-Charakter |
| 2 | `|raw` für Tagline/Headline | escapte Ausgabe | Formatierung in der Übersetzung — mit Risiko (FB-01) |
| 3 | Vorschau = 3 Beiträge | mehr/weniger | kompakte Startseite |

## Abdeckung der Akzeptanzkriterien

| AK | Erfüllt durch | Anmerkung |
|---|---|---|
| AK-01 | `home/index.html.twig` Abschnitte | |
| AK-02 | dieselbe Ansicht, Locale = en | |
| AK-03 | `findLatest(3)` + `{% if latestNews %}` | |
| AK-04 | Karten-Link auf `app_news_show(_en)` | |
| AK-05 | Anker + `scroll-behavior: smooth` | reduced-motion respektiert |
| AK-06 | `<link>` auf Google Fonts in `base.html.twig` | Fehlbestand — IP-Abfluss |
