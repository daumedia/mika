# App-Shell — Mika Ferreira

Stand: 2026-09-10 · aus `templates/base.html.twig`, `assets/` und der Security-/Routing-
Konfiguration rückwärts erfasst.

Was auf jeder Seite gleich ist: eine feste Kopfzeile mit Navigation und Sprachumschalter,
ein Inhaltsbereich und eine Fußzeile mit Kontakt- und Social-Verweisen. Alle Templates
erben von `base.html.twig`.

## Layout-Grundgerüst (`base.html.twig`)

```
<header> fixiert, .nav-glass ─ Logo · Desktop-Nav · Sprachpille · Mobile-Hamburger
<main>   {% block body %}
<footer> Name · Partei · E-Mail-/Social-Icons · .footer-divider
```

- `<html lang="{{ app.request.locale }}">` — Sprache aus dem aktuellen Locale.
- `<title>`-Standard: „Mika Ferreira — DP Kayl-Téiteng", je Seite überschrieben.
- **Favicon:** inline-SVG mit Emoji 🇱🇺 (`data:`-URI, kein Bild).
- `<body>` mit `bg-cream text-text font-body`, Flex-Spalte, `min-h-screen`.

## Navigation

- **Logo** „Mika **Ferreira**" verweist auf die Startseite im aktuellen Locale.
- **Menüpunkte:** Start (`nav.home`), News (`nav.news`), Kontakt (`nav.contact`) — je nach
  Locale auf `app_*` oder `app_*_en` gemappt.
- **Sprachpille LU/EN:** über eine `route_map` im Template wird die aktuelle Route auf ihr
  Gegenstück in der anderen Sprache abgebildet, inklusive Route-Parametern (`slug`).
  Fallback ist die Startseite, wenn die Route nicht in der Map steht.
- **Mobiles Menü:** Hamburger-Button, gesteuert vom Stimulus-Controller `nav`
  (`assets/controllers/nav_controller.js`) — `toggle()` blendet Menü und Icons um. Targets:
  `menu`, `openIcon`, `closeIcon`.

## Mehrsprachigkeit (Routing & Locale)

- Jede öffentliche Seite hat **zwei Routen** mit festem Locale-Präfix: `/lb/…` (Standard)
  und `/en/…`, gesetzt über `defaults: {_locale: …}` am jeweiligen Controller.
- `/` leitet per **301** auf `/lb` (`HomeController::root`).
- `LocaleSubscriber` (Priorität 20 auf `kernel.request`) übernimmt `_locale` aus den
  Request-Attributen, Standard `lb`.
- Der Admin-Bereich (`/admin`, `/login`, `/logout`) ist **nicht** locale-präfixiert und
  läuft auf Luxemburgisch (feste Template-Texte).

## Zugriffsschichten (Shell-Ebene)

- **`access_control`:** eine einzige Regel — `^/admin` erfordert `ROLE_ADMIN`. Alles
  andere ist öffentlich (kein Catch-all nötig, da keine weiteren geschützten Pfade).
- **Firewall `main`:** Form-Login (`app_login`, CSRF aktiv), Logout nach `app_home`,
  Provider = `Admin`-Entity über `username`. `dev`-Firewall schaltet Security für
  `/_profiler`, `/_wdt`, `/assets`, `/build` ab.
- Der Login-Controller leitet bereits angemeldete Nutzer direkt auf `app_admin`.
- Detaillierte Objekt-/Rollenregeln liegen bei den Features B01/B02, nicht hier.

## Asset-Pipeline

- Einstieg `assets/app.js` → lädt `stimulus_bootstrap.js` und `styles/app.css`.
- Stimulus/Turbo über Symfony UX (`controllers.json`: `turbo-core` eager).
- Tailwind v4 via `symfonycasts/tailwind-bundle` und `@tailwindcss/postcss`.

## Fehlbestand

- **Doppelte Asset-Pipeline — der auffälligste Shell-Befund.** Webpack Encore **und**
  AssetMapper/importmap sind gleichzeitig aktiv:
  - `importmap.php` und `webpack.config.js` definieren **beide** einen `app`-Entrypoint auf
    dieselbe `assets/app.js`.
  - `base.html.twig` gibt **beide** Tag-Sätze aus: `encore_entry_link_tags('app')` +
    `encore_entry_script_tags('app')` **und** `importmap('app')`.
  - Folge: Stimulus/Turbo und die App-Assets werden potenziell doppelt geladen. Eine der
    beiden Pipelines gehört entfernt (der Build-Commit hat Encore über das vorhandene
    AssetMapper-Setup gelegt, ohne AssetMapper auszubauen). → eigenes Aufräum-Feature.
- **Inline-`<script>` im Admin-News-Formular** (Sprach-Reiter + Slug-Generierung) statt
  eines Stimulus-Controllers — bricht das ansonsten konsequente Stimulus-Muster und läuft
  ohne CSP-Nonce.
- **Admin-Shell weicht ab:** kein gemeinsames Admin-Layout; Dashboard und News-Formular
  bauen ihren Kopf jeweils selbst aus dem Hero-Verlauf nach.
- **Keine 404-/Fehler-Templates** im Bestand (`templates/bundles/TwigBundle/Exception/`
  fehlt) — im Prod-Betrieb erscheint die Symfony-Standardseite. → Betriebsthema.
