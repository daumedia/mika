# B02 · Neuigkeiten verwalten — Systemdesign

Status: `rekonstruiert` · Stand: 2026-09-10 · Stack-Profil: `symfony-doctrine`

**Kein Code in diesem Dokument.**

## Überblick

CRUD für die `News`-Entity im geschützten Admin-Bereich. Das Dashboard listet alle
Beiträge; ein Formular mit Sprach-Reitern (LB/EN) pflegt beide Fassungen zugleich. Anlegen
und Bearbeiten laufen über ein Symfony-Formular (Form-CSRF automatisch), Löschen über ein
einfaches POST-Formular mit manuellem CSRF-Token. Alles hinter `ROLE_ADMIN`.

## Seiten und Routen

| Route | Zweck | Zugang |
|---|---|---|
| `GET /admin` | Dashboard: Tabelle aller Beiträge | `ROLE_ADMIN` |
| `GET/POST /admin/news/create` | neuen Beitrag anlegen | `ROLE_ADMIN` |
| `GET/POST /admin/news/{id}/edit` | Beitrag bearbeiten (404 bei unbekannter id) | `ROLE_ADMIN` |
| `POST /admin/news/{id}/delete` | Beitrag löschen (CSRF `delete-{id}`) | `ROLE_ADMIN` |

## Komponentenstruktur

```
AdminController
├── dashboard()   findBy([], publishedAt DESC) → Tabelle
├── createNews()  NewsType, publishedAt=now, persist+flash
├── editNews()    News (ParamConverter), NewsType, flush+flash
└── deleteNews()  CSRF-Prüfung → remove+flash
templates/admin/
├── dashboard.html.twig   Tabelle (Titel LB/EN, Kategorie, Datum, Ännern/Läschen)
└── news_form.html.twig    Sprach-Reiter LB/EN · Meta (Kategorie/Slug/Datum)
                           + Inline-<script> (Slug-Gen, Tab-Umschaltung)
Form/NewsType   titleLb/En, summaryLb/En, contentLb/En, category(Choice×4), slug, publishedAt
```

## Datenmodell

### Tabelle `news` (Details in `docs/datenmodell.md`)

| Feld | Typ | Pflicht | Bedeutung |
|---|---|---|---|
| `id` | INT AI | ja | PK |
| `title_lb` / `title_en` | VARCHAR(255) | ja | Titel je Sprache |
| `summary_lb` / `summary_en` | LONGTEXT | ja | Kurzfassung je Sprache |
| `content_lb` / `content_en` | LONGTEXT | ja | Inhalt je Sprache |
| `category` | VARCHAR(100) | ja | eine von 4 (nur im Formular erzwungen) |
| `slug` | VARCHAR(255), unique | ja | sprachübergreifend |
| `published_at` | DATETIME | ja | Redakteur setzt frei; steuert öffentliche Sichtbarkeit |

Beziehungen: keine (kein Autor-FK). Indizes: PK, Unique `slug`.

## Zugriffsregeln

| Wer | Darf lesen | Darf schreiben | Erzwungen durch |
|---|---|---|---|
| anonym | — (Weiterleitung `/login`) | — | `access_control ^/admin` |
| `ROLE_ADMIN` | alle Beiträge | alle Beiträge | `access_control ^/admin`; Löschen zusätzlich CSRF `delete-{id}` |

Kein Objekt-Voter — jeder Admin sieht/ändert jeden Beitrag (Fehlbestand FB-03).

## Missbrauchsschutz

| Endpunkt | Limit | Verhalten bei Überschreitung | Wo konfiguriert |
|---|---|---|---|
| `/admin/news/*` | keins | — | hinter Anmeldung, geringes Risiko; kein Rate Limit nötig |
| Löschen | CSRF-Token `delete-{id}` | ohne Token: keine Wirkung, Rückleitung | Controller + Template |

## Externe Dienste

Keine.

## Erkennbare Entscheidungen

| # | Entscheidung | Alternative | Warum so (soweit erkennbar) |
|---|---|---|---|
| 1 | Sprach-Reiter im selben Formular | getrennte Formulare je Sprache | beide Fassungen in einem Rutsch pflegbar |
| 2 | Slug clientseitig aus EN-Titel | serverseitiger Slugger | sofortiges Feedback; manueller Override möglich |
| 3 | Löschen mit manuellem CSRF | Symfony-Formular | einfaches Icon-Button-`<form>` |
| 4 | Kategorien im Formular fest | eigene Tabelle/Enum | vier feste Themen genügen — Grund plausibel, aber DB ungeschützt (FB-02) |

## Abdeckung der Akzeptanzkriterien

| AK | Erfüllt durch | Anmerkung |
|---|---|---|
| AK-01 | `dashboard()` + `dashboard.html.twig`-Tabelle | zeigt auch zukünftig datierte |
| AK-02 | `createNews()` + `NewsType` + Flash | |
| AK-03 | `NotBlank`-Constraints in `NewsType` | |
| AK-04 | `editNews()` + `flush` + Flash | |
| AK-05 | `deleteNews()` + CSRF + `remove` | |
| AK-06 | ParamConverter `News $article` → 404 | |
| AK-07 | Inline-`<script>` Slug-Generator | Fehlbestand FB-04 (kein Stimulus/CSP) |
| AK-08 | `access_control ^/admin` | einzige Schicht |
| AK-09 | `isCsrfTokenValid('delete-'~id)` | |
| AK-10 | Symfony-Form-CSRF (Standard) | |
