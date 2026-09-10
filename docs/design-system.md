# Design-System — Mika Ferreira

Stand: 2026-09-10 · aus `assets/styles/app.css` und den Twig-Templates rückwärts erfasst.

Tailwind CSS v4, konfiguriert über den `@theme`-Block in `assets/styles/app.css` (keine
`tailwind.config.js`). Die Marke ist bewusst „staatstragend": Navy + Creme + Gold, ein
Serifen-Display für Namen und Überschriften.

## Farben (Design-Tokens)

Definiert als `--color-*` im `@theme`-Block, damit als Tailwind-Utilities nutzbar
(`bg-navy`, `text-blue`, `border-cream-dark`, …).

| Token | Hex | Verwendung |
|---|---|---|
| `navy` | `#12213D` | Primär dunkel, Text, Hero-Grund, Buttons |
| `navy-light` | `#1E3460` | Theme-Karten, Hover |
| `navy-deep` | `#0B1526` | Hero-Verlauf oben |
| `cream` | `#FAF8F3` | Seitenhintergrund |
| `cream-dark` | `#EDE9E0` | Ränder, Trennlinien |
| `blue` | `#1A56B0` | Akzent, Links, Sektions­labels |
| `blue-light` | `#E8F0FC` | Icon-Flächen, Badges |
| `blue-vivid` | `#2563EB` | Theme-Icons |
| `gold` | `#C8922A` | Zierakzent, Regeln, Aufzähl­nummern |
| `gold-light` | `#F5E6C8` | Textmarkierung, Footer-Verlauf |
| `text` | `#12213D` | Fließtext (= navy) |
| `text-muted` | `#5A6580` | Sekundärtext |
| `text-soft` | `#8B95A8` | Labels, Metadaten |

Marken­fremde Literale (Social-Hover) stehen bewusst hart im Markup: `#1877F2` (Facebook),
`#E4405F` (Instagram), `#0A66C2` (LinkedIn).

## Typografie

| Rolle | Familie (Token) | Fallback | Einsatz |
|---|---|---|---|
| Display | `Fraunces` (`--font-display`) | Georgia, serif | Name, Überschriften, Aufzähl­nummern |
| Body | `Figtree` (`--font-body`) | system-ui, sans-serif | Fließtext, Navigation, Formulare |

Beide werden zur Laufzeit von **Google Fonts** geladen (`fonts.googleapis.com` /
`fonts.gstatic.com`, `preconnect` in `base.html.twig`). → Datenschutz-Punkt OF-03 im PRD:
IP-Abfluss an Google, lokale Auslieferung erwägen.

## Wiederkehrende Komponenten

Definiert als CSS-Klassen in `app.css`, nicht als Twig-Komponenten:

- **Buttons:** `.btn-primary` (Navy), `.btn-ghost` (transparent auf dunkel), `.btn-white`
  (weiß auf dunkel). Alle mit Hover-Anhebung.
- **Karten:** `.news-card`, `.theme-card` (Themen-Raster), `.contact-card`,
  `.inclusion-card` (linker Blau-/Gold-Balken). Alle mit Transitions.
- **Navigation:** `.nav-glass` (Blur-Kopfzeile), `.nav-link` (animierter Unterstrich),
  `.lang-toggle` (Pille LU/EN).
- **Sektions­label:** `.section-label` (kleine Kapitälchen mit Gold-Strich).
- **Formulare:** `.form-input`, `.form-label`, `.form-error-message`. **Nur im
  Admin-News-Formular tatsächlich genutzt** — die Kontaktseite hat kein Formular
  (siehe Fehlbestand).
- **Admin:** `.admin-table`, `.admin-tabs` / `.admin-tab` (Sprach-Reiter im News-Formular).
- **Hero:** `.hero-gradient` (radialer Navy-Verlauf), `.hero-pattern` (Punkt­raster),
  `.photo-frame` (konischer Ring ums Porträt).
- **Deko:** `.gold-rule`, `.footer-divider`, `body::after` (SVG-Korn-Textur, opacity 0.025).

## Bewegung & Barrierefreiheit

- Keyframes `fadeUp` / `fadeIn` / `slideInLeft` / `scaleIn` / `scrollPulse`, gestaffelt
  über `.fade-up-delay-1…5`.
- `@media (prefers-reduced-motion: reduce)` schaltet Animationen und Smooth-Scroll ab.
- `:focus-visible` mit sichtbarem blauem Ring; `::selection` in Gold. SVG-Icons tragen
  überwiegend `aria-hidden` bzw. `aria-label`.

## Fehlbestand / beobachteter Wildwuchs

Dokumentiert, **nicht** bereinigt — eine Aufräumaktion ist ein eigenes Feature:

- **Werte hart im Markup statt als Token.** Durchgängig arbitrary values wie
  `text-[0.7rem]`, `tracking-[0.15em]`, `h-[4.25rem]`, `w-[21rem]`. Schriftgrößen und
  Abstände sind nirgends als Skala definiert; jede Seite wählt eigene Zwischenwerte.
- **Formular-Stile ohne Formular.** `.form-input`, `.form-label`, `.form-error-message`
  und der CSS-Kommentar „CONTACT FORM" existieren, obwohl die Kontaktseite kein Formular
  rendert (`ContactType` ist toter Code, siehe B05).
- **Zwei Schrift-Fallback-Stacks** in Tokens definiert, aber Google Fonts sind harte
  Voraussetzung — bei Ausfall der CDN greift nur der Serifen-/Sans-Fallback.
- **Kein Dark-Mode**, keine Theme-Umschaltung — bewusst einfarbiges helles Design.
