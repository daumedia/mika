# B01 · Admin-Login — Systemdesign

Status: `rekonstruiert` · Stand: 2026-09-10 · Stack-Profil: `symfony-doctrine`

**Kein Code in diesem Dokument.** Beschreibt den Bestand, wie er greift.

## Überblick

Symfony-Formular-Login gegen die `Admin`-Entity. Die Firewall `main` schützt per
`access_control` alles unter `^/admin`; wer dort ohne Anmeldung landet, wird auf `/login`
geleitet. Passwörter liegen gehasht (`auto`). Es gibt genau eine Rolle, `ROLE_ADMIN`.

## Seiten und Routen

| Route | Zweck | Zugang |
|---|---|---|
| `GET/POST /login` | Login-Formular anzeigen und prüfen (`check_path` = `app_login`) | öffentlich |
| `GET /logout` | Abmelden, Ziel `app_home` | angemeldet |
| `… /admin*` | geschützter Bereich (Features B02) | `ROLE_ADMIN` |

## Komponentenstruktur

```
SecurityController
├── login()      Formular rendern; bei bestehender Sitzung → /admin
└── logout()     leer — von der Firewall abgefangen
templates/security/login.html.twig
├── Fehlerbanner (bei Auth-Fehler)
├── _username / _password
└── _csrf_token('authenticate')
Entity/Admin  (UserInterface, PasswordAuthenticatedUserInterface)
Command/CreateAdminCommand  (app:create-admin — Produktionsweg)
```

## Datenmodell

### Tabelle `admin`

| Feld | Typ | Pflicht | Bedeutung |
|---|---|---|---|
| `id` | INT AI | ja | Primärschlüssel |
| `username` | VARCHAR(180), unique | ja | Login-Kennung |
| `password` | VARCHAR(255) | ja | Hash (auto: bcrypt/argon) |
| `roles` | JSON | ja | Zusatzrollen; `getRoles()` ergänzt immer `ROLE_ADMIN` |

Beziehungen: keine. Indizes: PK `id`, Unique `username`.

## Zugriffsregeln

| Wer | Darf lesen | Darf schreiben | Erzwungen durch |
|---|---|---|---|
| anonym | öffentliche Seiten | — | `access_control` lässt alles außer `^/admin` durch |
| `ROLE_ADMIN` | Admin-Bereich | Admin-Bereich | `access_control: ^/admin → ROLE_ADMIN` + Firewall `main` |

Die Absicherung hängt an **einer** Regel (`^/admin`). Es gibt keine zweite Schicht
(kein RLS) — was diese Regel nicht deckt, ist ungeschützt (siehe Stack-Profil §3).

## Missbrauchsschutz

| Endpunkt | Limit | Verhalten bei Überschreitung | Wo konfiguriert |
|---|---|---|---|
| `/login` | **keins** (Fehlbestand FB-01) | unbegrenzte Versuche | — |

## Externe Dienste

Keine.

## Erkennbare Entscheidungen

| # | Entscheidung | Alternative | Warum so (soweit erkennbar) |
|---|---|---|---|
| 1 | Eigene `Admin`-Entity | generischer `User` | nur der Betreiber meldet sich an |
| 2 | Form-Login mit CSRF | Basic Auth / Login-Link | klassisches Web-Login, Standardschutz |
| 3 | `access_control ^/admin` ohne `#[IsGranted]` je Controller | Voter/Attribut je Aktion | ganzer Bereich an einem Regex — zulässig, aber einzige Schicht |
| 4 | Passwort-Provisionierung per CLI | Selbstregistrierung | kein Besucherkonten-Modell; Grund plausibel |

## Abdeckung der Akzeptanzkriterien

| AK | Erfüllt durch | Anmerkung |
|---|---|---|
| AK-01 | `form_login` + `default_target_path: app_admin` | |
| AK-02 | `AuthenticationUtils` (Fehler + last_username) | |
| AK-03 | `SecurityController::login` Redirect bei `getUser()` | |
| AK-04 | `access_control ^/admin` → Firewall-Redirect auf `app_login` | |
| AK-05 | Firewall `logout: target app_home` | |
| AK-06 | `enable_csrf: true`, Feld `_csrf_token('authenticate')` | |
| AK-07 | `password_hasher: auto`, `eraseCredentials()` leer | |
