# B06 · Zweisprachigkeit (LB/EN) — Systemdesign

Status: `rekonstruiert` · Stand: 2026-09-10 · Stack-Profil: `symfony-doctrine`

**Kein Code in diesem Dokument.**

## Überblick

Jede öffentliche Seite hat zwei Routen mit festem Locale-Präfix (`/lb…`, `/en…`), gesetzt
über `defaults: {_locale}` am Controller. Ein `LocaleSubscriber` übernimmt den `_locale`
aus den Request-Attributen (Standard `lb`) in den Request. Der Umschalter in der Kopfzeile
bildet die aktuelle Route über eine `route_map` auf ihr Gegenstück in der anderen Sprache
ab und erhält dabei die Route-Parameter (z. B. `slug`).

## Seiten und Routen

| Route | Zweck | Zugang |
|---|---|---|
| `GET /` | 301-Weiterleitung auf `/lb` | öffentlich |
| `/lb`, `/en` | Startseite je Sprache | öffentlich |
| `/lb/news`, `/en/news` (+ `/{slug}`) | Neuigkeiten je Sprache | öffentlich |
| `/lb/contact`, `/en/contact` | Kontakt je Sprache | öffentlich |
| `/admin*`, `/login`, `/logout` | **ohne** Locale-Präfix, nur lb | siehe B01/B02 |

## Komponentenstruktur

```
EventSubscriber/LocaleSubscriber   kernel.request (Prio 20) → request.setLocale(_locale|'lb')
Controller (je öffentliche Aktion)  zwei #[Route] mit defaults _locale = lb|en
HomeController::root()              301 / → app_home (/lb)
templates/base.html.twig
├── Nav-Links (locale-abhängige Route wählen)
└── lang-toggle  route_map current→counterpart (+ _route_params)  ×2 (Desktop, Mobil)
translations/messages.lb.yaml (Standard) · messages.en.yaml
```

## Datenmodell

Keins — querschnittliche Routing-/Übersetzungslogik. Beitragstexte tragen ihre
Übersetzung als eigene Spalten (siehe B02/B03), UI-Texte liegen in `translations/`.

## Zugriffsregeln

| Wer | Darf lesen | Darf schreiben | Erzwungen durch |
|---|---|---|---|
| alle | beide Sprachfassungen | — | öffentliche Routen |

Der Admin-Bereich ist bewusst nicht locale-präfixiert und damit vom Umschalter
ausgenommen.

## Missbrauchsschutz

Nicht anwendbar (keine Eingaben, keine Kosten).

## Externe Dienste

**Google Fonts** (Fraunces, Figtree) werden in `base.html.twig` von
`fonts.googleapis.com`/`fonts.gstatic.com` geladen — überträgt Besucher-IP an Google. Als
querschnittlicher App-Shell-Punkt hier vermerkt, Details in `docs/app-shell.md` und PRD
OF-03.

## Erkennbare Entscheidungen

| # | Entscheidung | Alternative | Warum so (soweit erkennbar) |
|---|---|---|---|
| 1 | URL-Präfix statt Cookie/Accept-Language | Session-/Cookie-Locale | teilbare, explizite URLs |
| 2 | zwei volle Routensätze | `/{_locale}`-Präfix-Import | explizit, aber redundant (FB-04) |
| 3 | Standard `lb` | `en` oder Auto-Erkennung | Landessprache |
| 4 | `route_map` im Template | zentrale Locale-Switch-Route | einfach, aber doppelt gepflegt (FB-03) |

## Abdeckung der Akzeptanzkriterien

| AK | Erfüllt durch | Anmerkung |
|---|---|---|
| AK-01 | `lang-toggle` + `route_map` + Locale-Routen | |
| AK-02 | `route_map` + `_route_params` (slug) | |
| AK-03 | `HomeController::root()` 301 | |
| AK-04 | `LocaleSubscriber` `defaultLocale='lb'` | |
| AK-05 | `route_map[...] ?? 'app_home(_en)'` Fallback | |
| AK-06 | Admin-Templates mit festen lb-Texten | bewusst |
