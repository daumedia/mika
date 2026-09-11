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
| Uptime-Überwachung | ⏳ offen — Account + Monitor anlegen | siehe unten |
| DB-Sicherung | ⏳ offen — in Coolify aktivieren | siehe unten |

## Offene Hand-off-Schritte (brauchen dein Konto / die Coolify-Oberfläche)

### 1 · Fehler-Tracking scharfschalten (Sentry, EU-Region)
1. Projekt in Sentry anlegen, **Region EU** (Frankfurt), Plattform „Symfony".
2. DSN kopieren, in Coolify als Env `SENTRY_DSN=<dsn>` setzen, App neu deployen.
3. Alarm auf **neue Fehlerarten** stellen (nicht jedes Auftreten), Empfänger deine E-Mail.
4. **Alarm testen:** eine künstliche Exception auslösen und prüfen, dass die Mail ankommt.
5. Sentry-DPA (Auftragsverarbeitung) im Konto bestätigen → in `docs/datenschutz.md` eintragen.

Datenschutz ist im Code schon berücksichtigt: `send_default_pii: false` (keine IPs,
keine Request-Bodies). In Dev/Test bleibt Sentry immer stumm.

### 2 · Uptime-Überwachung (UptimeRobot oder Better Stack)
1. Monitor Typ HTTP(S) auf `https://michael-ferreira.com/health`, Intervall 5 min,
   Erwartung: Statuscode 200 **und** Text enthält `ok`.
2. Zertifikats-Ablaufwarnung aktivieren (Coolifys Let's-Encrypt erneuert automatisch,
   die Warnung ist das Sicherheitsnetz).
3. Alarmweg: E-Mail **und** ein zweiter Kanal (Push/Telegram), damit ein Ausfall dich
   erreicht, auch wenn die Mail liegen bleibt.
4. **Alarm testen:** Container in Coolify kurz stoppen, prüfen, dass der Alarm ankommt.

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
