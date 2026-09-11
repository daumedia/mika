# 01 · Rechtstexte (Impressum & Datenschutzerklärung) — Aufgabenplan

Status: `tasked` · Stand: 2026-09-11

Ebenen laufen in Reihenfolge. `[P]` heißt: innerhalb dieser Ebene unabhängig von den
anderen `[P]`-Aufgaben, darf parallel an einen Subagenten gehen.

**Verifikation nach jeder Ebene** (Stack-Profil `symfony-doctrine`):

```bash
make fix-check                              # Exit ≠ 0 = etwas zu tun
php bin/console lint:container
php bin/console lint:twig templates/
php bin/console doctrine:schema:validate    # muss unverändert grün bleiben (keine Entität)
npm run build                               # Tailwind scannt die neuen Templates
php bin/phpunit                             # Funktionstests dieser Ebene
```

**Rot heißt anhalten.**

## Ebene 1 · Fundament — Daten und Konfiguration

**Entfällt.** Keine Entität, keine Migration, keine neue Abhängigkeit, keine
Umgebungsvariable, keine Plattformkonfiguration. Die Routen sind Attribut-basiert (Ebene 3),
die Zugriffsregel bleibt unverändert (Routen außerhalb `^/admin` sind öffentlich).

## Ebene 2 · Server — Logik und Validierung

**Entfällt.** Keine Services, keine Geschäftsregeln, keine Eingabeprüfung, kein externer
Dienst, keine Pseudonymisierung — die Seiten sind statisch und nur lesend.

## Ebene 3 · Schnittstellen

- [ ] **T01** · `src/Controller/LegalController.php` mit zwei Methoden `impressum()` und
      `datenschutz()`, je zwei `#[Route]` (`_locale` lb/en, Namen `app_impressum(_en)`,
      `app_datenschutz(_en)`, gleiche Slugs `impressum`/`datenschutz`), rendert die
      jeweilige Seiten-Template. Kein `#[IsGranted]` (öffentlich über `access_control`).
      Funktionstest (`tests/Functional/`): beide Seiten in beiden Sprachen liefern **ohne
      Login** 200, kein Redirect auf `/login`. — `AK-02, AK-03, AK-04, AK-10`

## Ebene 4 · Oberfläche

Seitenzustände hier: die Rechtsseiten haben nur den **gefüllten** Zustand (statischer
Inhalt, kein Laden, kein leerer/Fehler-Zustand im Fachsinn) — Robustheit bei fehlender
Sprachfassung deckt Ebene 5.

- [ ] **T02** · `templates/legal/_layout.html.twig` (erbt `base.html.twig`): lesbarer
      Prosa-Container (Textbreite, Fraunces-Überschriften, Figtree-Fließtext mit
      Design-Tokens), `{% block title %}` je Seite, und ein Baustein, der bei `_locale=en`
      den Hinweis „luxemburgische Fassung ist maßgeblich" einblendet (Übersetzungsschlüssel
      `legal.authoritative_note`). — `AK-06, AK-11`
- [ ] **T03** `[P]` · `templates/legal/impressum.html.twig` (erbt `_layout`) plus
      `templates/legal/content/impressum.lb.html.twig` und `impressum.en.html.twig`:
      Betreiber-Name, Kontakt-E-Mail als `mailto`, Postanschrift, DP Kayl-Téiteng,
      Inhaltsverantwortung — **nur** diese freigegebenen Betreiber-Angaben, keine
      Dritt-Daten. Werte aus OF-01. Funktionstest prüft, dass die Pflichtangaben erscheinen.
      — `AK-07, AK-04, AK-12`
- [ ] **T04** `[P]` · `templates/legal/datenschutz.html.twig` (erbt `_layout`) plus
      `templates/legal/content/datenschutz.lb.html.twig` und `datenschutz.en.html.twig`:
      Abschnitte gespiegelt aus `docs/datenschutz.md` — Verantwortlicher, keine Tracker /
      keine einwilligungspflichtigen Cookies, Server-/Zugriffslogs, Fehler-Tracking Sentry
      (EU), Hosting (OF-02), `mailto`-Kontakt (kein Formular), Betroffenenrechte + Beschwerde
      bei der **CNPD**. Kein Google-Fonts-/Newsletter-/Formular-Text. — `AK-08, AK-04`
- [ ] **T05** `[P]` · `templates/base.html.twig`: (a) Footer-Zeile mit zwei Links
      „Impressum" · „Datenschutz" (Übersetzungsschlüssel `legal.impressum` /
      `legal.datenschutz`, `path()` auf die locale-passende Route) — erscheint auf jeder
      öffentlichen Seite; (b) die Sprachpillen-`route_map` in **beiden** Zweigen (LU/EN) und
      an **beiden** Stellen (Desktop-Nav + Mobile-Menü) um die vier neuen Routen ergänzen,
      damit die Umschaltung auf die Gegenfassung führt statt auf die Startseite. Neue
      Übersetzungsschlüssel in `translations/messages.lb.yaml` + `messages.en.yaml`
      (`legal.impressum`, `legal.datenschutz`, `legal.authoritative_note`, Seitentitel).
      — `AK-01, AK-05`

## Ebene 5 · Feinschliff

- [ ] **T06** · Randfälle + Barrierefreiheit: fehlende Sprachfassung eines
      Inhaltsbausteins fällt sauber auf LU zurück (kein 500, kein roher
      Übersetzungsschlüssel); Sprachpille auf einer Rechtsseite landet nachweislich auf der
      Gegenfassung, **nicht** auf der Startseite; unbekanntes Locale-Präfix verhält sich wie
      andere Seiten; sinnvolle Überschriftenstruktur und tastaturerreichbare Links.
      Abschließender Abgleich der Datenschutz-Seite gegen `docs/datenschutz.md` (nichts
      Falsches behauptet). — `EC-01, EC-02, EC-03, EC-04, AK-09`

## Abdeckung

| AK | Aufgaben |
|---|---|
| AK-01 | T05 |
| AK-02 | T01, T03 |
| AK-03 | T01, T04 |
| AK-04 | T01, T03, T04 |
| AK-05 | T05 |
| AK-06 | T02 |
| AK-07 | T03 |
| AK-08 | T04 |
| AK-09 | T04, T06 |
| AK-10 | T01 |
| AK-11 | T02 |
| AK-12 | T03 |
| EC-01 | T06 |
| EC-02 | T06 |
| EC-03 | T06 |
| EC-04 | T06 (Link nur auf öffentlichen Seiten gefordert) |

**AK ohne Aufgabe:** Katalog-Abschnitte 4 (Missbrauch/Kosten) und 6 (Geheimnisse) — kein
Aufwand, „trifft nicht zu": statische, nur lesende Seiten ohne Eingabe, Kosten oder
Geheimnisse. Bewusst ohne Aufgabe, nicht vergessen.

**Aufgabe ohne AK:** keine.

## Parallelisierung

**Ebene 4:** T03, T04, T05 laufen gleichzeitig — sie berühren getrennte Dateien:
- T03 → `templates/legal/impressum.html.twig`, `templates/legal/content/impressum.{lb,en}.html.twig`
- T04 → `templates/legal/datenschutz.html.twig`, `templates/legal/content/datenschutz.{lb,en}.html.twig`
- T05 → `templates/base.html.twig`, `translations/messages.{lb,en}.yaml`

T02 (`templates/legal/_layout.html.twig`) läuft **vor** T03/T04, weil beide es erben —
eigene Datei, aber logische Vorbedingung. T05 hängt nicht von T02 ab, wird der Klarheit
halber trotzdem nach T02 eingeplant.

Ebene 3 hat nur T01 — nichts zu parallelisieren.

## Vor dem Bauen

- [ ] Feature-Branch: `git checkout -b feature/01-rechtstexte`
- [ ] **OF-01** — konkrete Impressum-Angaben (Name-Schreibweise, Kontakt-E-Mail,
      Postanschrift) vom Betreiber. Ohne sie baut T03 mit **klar markiertem Platzhalter**;
      AK-07 ist dann erst nach Nachreichen erfüllt.
- [ ] **OF-02** — endgültiger Hosting-Anbieter + Datenregion für die Datenschutz-Seite
      (T04/AK-09). Ohne sie Hosting-Abschnitt als Platzhalter markieren.
- [ ] Datenschutz-Textentwurf wird in T04 aus `docs/datenschutz.md` abgeleitet; finale
      Rechtsprüfung durch eine Fachperson bleibt OF-03 (nach dem Bau).
- [ ] Lokale DB läuft (`make start`/Docker) für die Funktionstests gegen die Testdatenbank.
