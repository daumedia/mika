# B01 · Admin-Login — Spezifikation

Status: `rekonstruiert` · Stand: 2026-09-10 · rückwärts aus dem Bestand erfasst

> Beschreibt, **was der Code heute tut**. Fragwürdiges ist mit ⚠ markiert, Lücken stehen
> unter *Fehlbestand* — kein stillschweigendes Zurechtrücken.

## Zweck

Der Betreiber meldet sich mit Benutzername und Passwort an und erhält damit Zugang zum
Admin-Bereich. Ohne Anmeldung ist `/admin` nicht erreichbar.

## Abhängigkeiten

| Braucht | Status | Warum |
|---|---|---|
| — | — | keine; B01 ist die Wurzel der Zugriffskette |

## User Stories

- **US-01** · Als Betreiber möchte ich mich sicher anmelden, damit nur ich Neuigkeiten
  pflegen kann.
- **US-02** · Als Betreiber möchte ich mich abmelden, damit an einem fremden Gerät keine
  offene Sitzung zurückbleibt.

## Nicht im Scope

- Registrierung, Passwort-Reset, Konten für Besucher (gehört nicht hierher — bewusst kein
  Besucherbereich, siehe PRD Nicht-Ziele).
- Rollen jenseits von `ROLE_ADMIN` (es gibt nur den Betreiber).

## Akzeptanzkriterien

- **AK-01** · Angenommen, ein Admin-Konto existiert, wenn Benutzername und Passwort korrekt
  über das Login-Formular abgeschickt werden, dann ist der Nutzer angemeldet und landet auf
  dem Dashboard `/admin`.
- **AK-02** · Angenommen, das Passwort ist falsch, wenn abgeschickt wird, dann erscheint
  eine Fehlermeldung (übersetzter `security`-Schlüssel), das Benutzername-Feld bleibt
  gefüllt, und der Nutzer ist nicht angemeldet.
- **AK-03** · Angenommen, der Nutzer ist bereits angemeldet, wenn `/login` aufgerufen wird,
  dann wird direkt auf `/admin` weitergeleitet, ohne das Formular erneut zu zeigen.
- **AK-04** · Angenommen, der Nutzer ist nicht angemeldet, wenn `/admin` oder ein
  Unterpfad aufgerufen wird, dann wird auf `/login` weitergeleitet.
- **AK-05** · Angenommen, der Nutzer ist angemeldet, wenn er sich abmeldet (`/logout`),
  dann ist er abgemeldet und landet auf der Startseite `/lb`.

### Datenschutz und Missbrauchsschutz

- **AK-06** · Angenommen, ein Login-POST trägt kein gültiges CSRF-Token (`authenticate`),
  wenn er abgeschickt wird, dann wird die Anmeldung abgelehnt (`enable_csrf: true`).
- **AK-07** · Angenommen, ein Passwort ist gesetzt, dann wird es nur als Hash gespeichert
  (`password_hasher: auto`) und `eraseCredentials()` hält kein Klartextpasswort im Objekt.
- §1 Personendaten / §2 externe Dienste / §5 Löschen: **trifft nicht zu** — die einzige
  Identität ist der Betreiber selbst; es gehen keine Daten an Dritte, es gibt kein
  Besucherkonto zu löschen.

## Edge Cases

- **EC-01** · Leeres Benutzername- oder Passwortfeld → Browser-`required` verhindert das
  Absenden; serverseitig scheitert die Authentifizierung ohnehin.
- **EC-02** · `/logout` wird per GET ausgelöst (Link im Dashboard) → Abmeldung erfolgt.

## Fehlbestand

Nicht vorhanden, aus dem Code belegt. Kein Kriterium — `sdd-qa` nimmt es als Suchliste.

- **FB-01 · Kein Rate Limit / kein Login-Throttling auf `/login`.** `security.yaml` enthält
  keinen `login_throttling`-Abschnitt, `SecurityController::login` zählt nichts. Folge:
  unbegrenzte Passwortversuche (Brute Force). Sicherheitskatalog §4 verlangt es für die
  Anmeldung ausdrücklich.
- **FB-02 · Dev-Fixture mit schwachem Passwort.** `AdminFixtures.php:22` legt Admin `mika`
  mit Passwort `admin` an. Folge: Wird die Fixture je in Produktion geladen (oder das
  Passwort nie geändert), ist der Admin-Bereich trivial übernehmbar. **Kritisch, sobald
  produktionsnah.** Produktionsweg ist `app:create-admin`.
- **FB-03 · `/logout` ohne CSRF-Schutz per GET.** Ein fremder Link/`<img>` kann den
  Betreiber unbemerkt abmelden. Geringe Schwere, aber real.
- **FB-04 · `getRoles()` hängt immer `ROLE_ADMIN` an**, unabhängig vom gespeicherten
  `roles`-Array (`Admin.php:62`). Ein eingeschränkter Redakteur ist nicht darstellbar; jede
  Admin-Zeile ist Vollzugriff. Bei einem Betreiber unkritisch, als Struktur notiert.

## Offene Fragen

- **OF-01** · Ist ein Login-Throttling gewünscht (FB-01)? Empfehlung: ja, fünf Versuche /
  15 min. — Betreiber entscheidet, vor Prod-Deploy.

## Decision Log

| # | Frage | Entscheidung | Begründung |
|---|---|---|---|
| 1 | Eigene `Admin`-Entity statt generischem `User`? | eigene Entity | nur der Betreiber meldet sich an; kein Besucherkonten-Modell nötig |
| 2 | CSRF am Login? | aktiv (`enable_csrf: true`) | Standardschutz gegen Login-CSRF |
