# Datenschutz — Mika Ferreira

Stand: 2026-09-11 · Datenschutzstufe: **A** (öffentliche Präsenzseite, keine
Besucherkonten, keine Verarbeitung von Besucher-Personendaten im Regelbetrieb)

> Dieses Dokument sammelt den Verarbeitungsstand und offene Punkte. Es ist **keine
> Rechtsberatung** und ersetzt keine veröffentlichte Datenschutzerklärung.

## Verarbeitungsverzeichnis

| Verarbeitung | Zweck | Kategorien | Rechtsgrundlage | Frist |
|---|---|---|---|---|
| Admin-Konto | Pflege der Neuigkeiten | Benutzername, Passwort-Hash (Betreiber selbst) | berechtigtes Interesse (Betrieb der Seite) | bis Konto gelöscht |
| Neuigkeiten (News) | redaktionelle Inhalte | Titel/Text/Datum — **kein** Besucherbezug | — | bis Beitrag gelöscht |
| Server-/Zugriffslogs | Betrieb, Fehlersuche | IP + Request (technisch, kurzlebig) | berechtigtes Interesse | Hoster-Vorgabe, kurz halten |
| Fehler-Tracking (Sentry) | Stabilität | Fehlerkontext **ohne** PII (`send_default_pii: false`) | berechtigtes Interesse | Sentry-Retention (EU) |

Kontaktseite: nur `mailto:`-Verweise, **kein** serverseitiges Formular — es entsteht keine
Verarbeitung auf dem Server (der Versand läuft im E-Mail-Programm des Besuchers).

## Auftragsverarbeiter

| Dienst | Rolle | Region | AV-Vertrag | Status |
|---|---|---|---|---|
| Hosting — **Hostinger** (Hostinger Operations UAB) | Betrieb App + DB | ✅ **DE (EU)** bestätigt — Rechenzentrum Düsseldorf (IP-Geolokalisierung 2026-09-11) | **offen** — AV-Vertrag/DPA bei Hostinger abschließen und ablegen | ✅ aktiv |
| Sentry | Fehler-Tracking | ✅ EU (Frankfurt) bestätigt | ✅ DPA bestätigt 2026-09-11 | ✅ aktiv seit 2026-09-11 |

## Drittlandübermittlung

**Keine Drittlandübermittlung.** Sentry-Region **EU** (Frankfurt) und Hosting bei
**Hostinger in Deutschland (EU)** sind beide bestätigt.

## Gelöst

- **Google Fonts**: früher zur Laufzeit von Google-Servern geladen (Besucher-IP an Google).
  Seit 2026-09-11 lokal aus `public/fonts/` ausgeliefert — **kein IP-Abfluss** mehr.

## Offene Punkte

- **Impressum + Datenschutzerklärung** veröffentlicht? Für eine öffentliche Wahlkampfseite
  in LU erwartet. Existenz/Fassung prüfen und hier mit Datum vermerken. (Inhalt = Aufgabe
  des Betreibers, ggf. mit fachlichem Rat.)
- **Hosting-AV-Vertrag/DPA bei Hostinger** abschließen und Ablageort notieren (Region DE/EU
  ist bestätigt).
- ~~Sentry-DPA + EU-Region~~ ✅ erledigt 2026-09-11 (EU bestätigt, DPA bestätigt).
- **Server-Log-Aufbewahrung** beim Hoster prüfen und kurz halten.
