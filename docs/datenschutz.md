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
| Hosting — **Hostinger** (Hostinger International Ltd., CY) | Betrieb App + DB | ✅ **DE (EU)** bestätigt — Rechenzentrum Düsseldorf (IP-Geolokalisierung 2026-09-11) | ✅ DPA gilt über die ToS (Art. 28, EU-SCC Modul 2/3), https://www.hostinger.com/legal/dpa (Stand 2026-08-18), notiert 2026-09-11 | ✅ aktiv |
| Sentry | Fehler-Tracking | ✅ EU (Frankfurt) bestätigt | ✅ DPA bestätigt 2026-09-11 | ✅ aktiv seit 2026-09-11 |

## Drittlandübermittlung

**Keine Drittlandübermittlung.** Sentry-Region **EU** (Frankfurt) und Hosting bei
**Hostinger in Deutschland (EU)** sind beide bestätigt.

## Gelöst

- **Google Fonts**: früher zur Laufzeit von Google-Servern geladen (Besucher-IP an Google).
  Seit 2026-09-11 lokal aus `public/fonts/` ausgeliefert — **kein IP-Abfluss** mehr.

## Offene Punkte

- ~~Impressum + Datenschutzerklärung veröffentlicht + fachlich geprüft~~ ✅ erledigt
  2026-09-11 (Feature 01 live unter `/impressum` und `/datenschutz`; Texte vom Betreiber
  fachlich abgesegnet, OF-03).
- ~~Hosting-AV-Vertrag/DPA bei Hostinger~~ ✅ erledigt 2026-09-11 — Hostingers DPA (Art. 28,
  EU-SCC) gilt automatisch über die Zustimmung zu den Terms of Service (keine separate
  Unterschrift nötig), Ablage: https://www.hostinger.com/legal/dpa. Sub-Auftragsverarbeiter
  sind über die SCC abgedeckt; neue mit 10-Tage-Widerspruchsrecht.
- ~~Sentry-DPA + EU-Region~~ ✅ erledigt 2026-09-11 (EU bestätigt, DPA bestätigt).
- **Server-Log-Aufbewahrung** beim Hoster prüfen und kurz halten.
