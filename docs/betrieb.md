# Betrieb — Mika Ferreira

Stand: 2026-09-11 · Umgebung: Coolify (Docker/FrankenPHP), MySQL 8 · Live:
https://michael-ferreira.com · Datenschutzstufe: **A**

Eingerichtet über `sdd-betrieb`. Was hier steht, ist der Betriebszustand — nicht der Code.

## Was eingerichtet ist

| Bereich | Zustand | Wo |
|---|---|---|
| Rate Limit Login | ✅ aktiv (5 Versuche → Sperre), auf Prod verifiziert | `config/packages/security.yaml` (`login_throttling`) |
| Security-Header | ✅ X-Content-Type-Options, X-Frame-Options: DENY, Referrer-Policy, Permissions-Policy, HSTS (nur https) | `src/EventSubscriber/SecurityHeadersSubscriber.php` |
| PHP-Version verborgen | ✅ `expose_php = Off` (kein `X-Powered-By`) | `Dockerfile` (zz-app.ini) |
| HTTPS-Erkennung | ✅ Trusted-Proxy → korrekte `https://`-URLs hinter Coolify | `config/packages/framework.yaml` |
| Schriften lokal | ✅ Fraunces + Figtree aus `public/fonts/` (keine Besucher-IP an Google) | `assets/styles/app.css`, `templates/base.html.twig` |
| Fehler-Tracking | ⏳ Code verdrahtet, **inaktiv** bis `SENTRY_DSN` gesetzt | `config/packages/sentry.yaml` |
| Health-Endpunkt | ✅ `/health` → `ok` | `src/Controller/HealthController.php` |
| Uptime-Überwachung | ✅ Uptime Kuma (**separater Server**), `/health` Keyword `ok`, Telegram-Alarm; **DOWN→Alarm→Recovery getestet 2026-09-11** | — |
| DB-Sicherung | ⏳ offen — in Coolify aktivieren | siehe unten |

**Live-verifiziert am 2026-09-11** (nach Deploy auf `master`): Security-Header inkl. HSTS
gesetzt, `X-Powered-By` weg, `/admin`-Redirect nun `https://`, Google-Fonts-Link entfernt,
6 lokale `/fonts/*.woff2` im CSS, Kernrouten 200. Sentry bootet inert (kein DSN).

## Offene Hand-off-Schritte (brauchen dein Konto / die Coolify-Oberfläche)

### 1 · Fehler-Tracking scharfschalten (Sentry, EU-Region)
1. Projekt in Sentry anlegen, **Region EU** (Frankfurt), Plattform „Symfony".
2. DSN kopieren, in Coolify als Env `SENTRY_DSN=<dsn>` setzen, App neu deployen.
3. Alarm auf **neue Fehlerarten** stellen (nicht jedes Auftreten), Empfänger deine E-Mail.
4. **Alarm testen:** eine künstliche Exception auslösen und prüfen, dass die Mail ankommt.
5. Sentry-DPA (Auftragsverarbeitung) im Konto bestätigen → in `docs/datenschutz.md` eintragen.

Datenschutz ist im Code schon berücksichtigt: `send_default_pii: false` (keine IPs,
keine Request-Bodies). In Dev/Test bleibt Sentry immer stumm.

### 2 · Uptime-Überwachung — ✅ erledigt (2026-09-11)
**Uptime Kuma** auf einem **separaten Server** prüft `https://michael-ferreira.com/health`
(Keyword-Monitor, Keyword `ok`). Alarm über **Telegram**. Die Alarmkette wurde getestet:
Keyword kurz auf `zzz-test` → Monitor DOWN → Telegram-Alarm kam → Keyword zurück → grün;
`/health` war laut Gegenprobe die ganze Zeit `ok`.

Noch sinnvoll (optional): in Uptime Kuma die **Zertifikats-Ablaufwarnung** aktivieren —
Coolifys Let's-Encrypt erneuert zwar automatisch, die Warnung ist das Sicherheitsnetz.

### 3 · Datenbank-Sicherung (Coolify)
1. Bei der MySQL-Ressource in Coolify **Scheduled Backups** aktivieren, täglich.
2. Ablageziel außerhalb des Servers wählen (S3-kompatibel), sonst liegt die Sicherung
   auf derselben Maschine wie das Original.
3. **Wiederherstellung proben:** eine Sicherung in eine leere DB einspielen — eine
   Sicherung, die nie eingespielt wurde, ist nur eine Datei.

## Noch nicht angefasst (bewusst)

- **Content-Security-Policy (erzwingend).** Blockiert durch die doppelte Asset-Pipeline
  (Encore + AssetMapper mit Inline-importmap-Script) und das Inline-`<script>` im
  News-Formular (BF-07). Eine strikte CSP braucht dort Nonces und würde die Seite sonst
  zerlegen. Gehört an die Bereinigung der Asset-Pipeline gekoppelt — eigener Schritt.
- **Niedrige QA-Befunde** BF-05, 06, 09–12 (`features/befunde.md`) — Aufräum-Feature,
  kein Betriebsthema.

## Regelmäßig zu tun

| Wann | Was |
|---|---|
| wöchentlich | neue Fehlerarten in Sentry durchsehen |
| monatlich | Kosten der Dienste (Coolify-Host, Sentry) gegen die Erwartung halten |
| monatlich | `composer outdated` / `npm outdated`, Sicherheitshinweise prüfen |
| vierteljährlich | Wiederherstellungsprobe der DB-Sicherung |
| bei jedem neuen Dienst | AV-Vertrag, Verarbeitungsverzeichnis, Datenschutzhinweise |

## Deploy-Erinnerung

Coolify baut aus Branch **`master`**, GitHub-Default ist `main`. Nach jedem Merge in
`main` muss der Stand auch nach `master`: `git push origin main:master`. Sonst geht keine
Änderung live.
