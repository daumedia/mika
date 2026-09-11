# 01 · Rechtstexte (Impressum & Datenschutzerklärung) — Spezifikation

Status: `planned` · Stand: 2026-09-11

## Zweck

Besucher der Wahlkampfseite finden zwei rechtliche Pflichtseiten — Impressum und
Datenschutzerklärung —, die von jeder Seite aus über die Fußzeile erreichbar und
zweisprachig (LB/EN) sind. Vorher gab es sie nicht; für eine öffentliche Präsenzseite
in Luxemburg sind sie erwartet.

## Abhängigkeiten

| Braucht | Status | Warum |
|---|---|---|
| B06 Zweisprachigkeit | review (production-ready) | Die Seiten sind zweisprachig; die Sprachpille muss zwischen der LU- und der EN-Fassung **derselben** Rechtsseite umschalten (die Sprach-`route_map` im Footer/Header muss die neuen Routen kennen, sonst fällt sie auf die Startseite zurück). |

Der Footer, in dem die Links erscheinen, liegt in `base.html.twig` (App-Shell) und ist auf
jeder öffentlichen Seite vorhanden.

## User Stories

- **US-01** · Als Besucher möchte ich Impressum und Datenschutzerklärung leicht finden,
  damit ich weiß, wer die Seite betreibt und wie mit Daten umgegangen wird.
- **US-02** · Als Betreiber möchte ich die erwarteten Pflichtangaben bereitstellen, damit
  die Wahlkampfseite rechtskonform erreichbar ist.
- **US-03** · Als englischsprachiger Besucher möchte ich die Texte auf Englisch lesen, mit
  einem Hinweis, welche Fassung rechtlich maßgeblich ist.

## Nicht im Scope

- **Kein Cookie-Consent-Banner** — die Seite setzt keine Tracker und keine
  einwilligungspflichtigen Cookies (nur die technische Symfony-Session). Bewusst weggelassen.
- **Kein Admin-CRUD für die Rechtstexte** — sie sind statisch (in Templates/Übersetzungen).
  Falls sie je oft geändert werden sollen: eigenes Feature mit eigener Nummer.
- **Keine rechtsverbindliche Texterstellung oder Rechtsberatung** — die verbindlichen Texte
  prüft/verantwortet eine Fachperson bzw. der Betreiber. Dieses Feature liefert Seitengerüst
  und einen faktischen Datenschutz-Entwurf, keine Rechtsprüfung.
- **Kein Kontaktformular** — Kontakt bleibt `mailto`/Social (siehe B05).
- **Kein Barrierefreiheits-Audit** der Seiten — eigenes Thema, falls gewünscht.

## Akzeptanzkriterien

Jedes Kriterium ist ohne Codekenntnis prüfbar (Beispiel-URLs sind Illustration; die genauen
Pfade legt `sdd-architektur` fest).

- **AK-01** · Angenommen ein Besucher ist auf einer beliebigen öffentlichen Seite
  (Start, News, Kontakt), wenn er in die Fußzeile schaut, dann sieht er zwei Verweise
  „Impressum" und „Datenschutz" (bzw. deren Übersetzung), die zu den jeweiligen Seiten führen.
- **AK-02** · Angenommen ein Besucher klickt im Footer auf „Impressum", wenn die Seite lädt,
  dann erscheint die Impressum-Seite mit Statuscode 200 und Impressum-Inhalt — kein 404 und
  kein Rücksprung auf die Startseite.
- **AK-03** · Angenommen ein Besucher klickt im Footer auf „Datenschutz", wenn die Seite
  lädt, dann erscheint die Datenschutzerklärung mit Statuscode 200.
- **AK-04** · Angenommen der Besucher ist im luxemburgischen Sprachzweig (`/lb/…`), wenn er
  Impressum bzw. Datenschutz öffnet, dann ist der Inhalt auf Luxemburgisch; im englischen
  Zweig (`/en/…`) auf Englisch.
- **AK-05** · Angenommen ein Besucher ist auf der luxemburgischen Impressum-Seite, wenn er
  die Sprachpille auf EN stellt, dann landet er auf der **englischen Impressum-Seite** — nicht
  auf der Startseite. Dasselbe gilt für die Datenschutzseite.
- **AK-06** · Angenommen ein Besucher öffnet die **englische** Fassung von Impressum oder
  Datenschutz, wenn die Seite lädt, dann steht sichtbar ein Hinweis, dass die
  **luxemburgische Fassung rechtlich maßgeblich** ist.
- **AK-07** · Angenommen ein Besucher öffnet das Impressum, wenn er es liest, dann findet er:
  den Namen des Betreibers (Mika/Michael Ferreira), eine Kontakt-E-Mail als `mailto`-Verweis,
  eine Postanschrift, die Parteizugehörigkeit (DP Kayl-Téiteng) und die Angabe, wer für den
  Inhalt verantwortlich ist.
- **AK-08** · Angenommen ein Besucher öffnet die Datenschutzerklärung, wenn er sie liest,
  dann nennt sie mindestens: dass **keine** Besucher-Tracker/Analyse und **keine**
  einwilligungspflichtigen Cookies eingesetzt werden; die Verarbeitung von Server-/Zugriffs-
  logs; das Fehler-Tracking über **Sentry (EU-Region)**; das Hosting; das Kontaktprinzip per
  `mailto` (kein datenspeicherndes Formular); und die Rechte der betroffenen Person (Auskunft,
  Löschung, Beschwerde bei der **CNPD**).
- **AK-09** · Angenommen die Datenschutzerklärung wird gegen `docs/datenschutz.md` gehalten,
  wenn man sie prüft, dann beschreibt sie nur tatsächlich stattfindende Verarbeitungen und
  behauptet nichts Falsches — insbesondere **keine** Google-Fonts-Datenübertragung (Schriften
  werden lokal ausgeliefert), **kein** Newsletter, **kein** Kontaktformular.
- **AK-10** · Angenommen ein nicht angemeldeter Besucher, wenn er Impressum oder Datenschutz
  aufruft, dann sind beide Seiten ohne Anmeldung zugänglich (kein Redirect auf den Login).
- **AK-11** · Angenommen eine der Rechtsseiten wird geladen, wenn man den Seitentitel ansieht,
  dann trägt sie einen eigenen, sprachrichtigen Titel (z. B. „Impressum — Mika Ferreira").

### Datenschutz und Missbrauchsschutz

Stufe **A** (PRD). Der Sicherheitskatalog (`~/.claude/sdd/sicherheit.md`) ist auf die
Abschnitte 4 (Missbrauch/Kosten) und 6 (Geheimnisse) verkürzt; das Feature liefert statische,
nur-lesbare Seiten ohne Eingaben.

- **AK-12** · Angenommen das Impressum veröffentlicht bewusst die Kontaktdaten des Betreibers
  (Name, E-Mail, Postanschrift), wenn man die Seiten prüft, dann werden **genau** diese vom
  Betreiber freigegebenen Angaben gezeigt und **keine** weiteren personenbezogenen Daten
  Dritter. (Es ist die einzige personenbezogene Veröffentlichung des Features und erfolgt auf
  ausdrücklichen Wunsch des Betreibers.)
- **Abschnitt 4 — trifft nicht zu, weil** die Seiten keine Eingaben entgegennehmen, nichts
  berechnen, keine externen Aufrufe auslösen und keine Kosten verursachen: kein Rate Limit,
  keine Upload-Prüfung nötig.
- **Abschnitt 6 — trifft nicht zu, weil** die Seiten keine Schlüssel oder Geheimnisse
  verwenden (statischer Inhalt).

## Edge Cases

- **EC-01** · Die EN-Route einer Rechtsseite fehlt in der Sprach-`route_map` → die Sprachpille
  darf **nicht** stumm auf die Startseite zurückfallen; das ist genau der Fehler, den AK-05
  ausschließt.
- **EC-02** · Ein Übersetzungsschlüssel fehlt → es erscheint **kein** roher Schlüssel und die
  Seite bleibt lesbar (keine Ausnahme, kein 500).
- **EC-03** · Direktaufruf einer Rechtsseite mit unbekanntem/fehlendem Locale-Präfix → verhält
  sich wie die übrigen öffentlichen Seiten (Standard-Locale bzw. reguläres 404), keine
  Sonderbehandlung.
- **EC-04** · Der Admin-Bereich erbt den öffentlichen Footer nicht zwingend → verbindlich ist,
  dass die Links auf **allen öffentlichen** Seiten erscheinen; im Admin ist es nicht gefordert.

## Offene Fragen

- **OF-01** · Genaue Impressum-Angaben (Namensschreibweise, Kontakt-E-Mail, Postanschrift)
  liefert der Betreiber, bevor gebaut wird. Entscheidet: Betreiber.
- **OF-02** · Endgültiger Hosting-Anbieter und Datenregion (EU?) müssen feststehen, damit die
  Datenschutzerklärung das Hosting korrekt benennt — hängt an PRD OF-01 und dem offenen Punkt
  in `docs/datenschutz.md`. Entscheidet: Betreiber, vor Veröffentlichung.
- **OF-03** · Prüfung der finalen Texte durch eine Fachperson/CNPD-Abgleich (empfohlen, da
  dieses Feature keine Rechtsberatung leistet). Entscheidet: Betreiber.
- **OF-04** · EN-Slugs (`imprint`/`privacy`) gegenüber gleichen Slugs (`impressum`/
  `datenschutz`) in beiden Sprachen — Detail für `sdd-architektur`.

## Decision Log

| # | Frage | Entscheidung | Begründung |
|---|---|---|---|
| 1 | Zuschnitt | Zwei getrennte **statische** Seiten (Impressum, Datenschutz), Footer-Link | Übliche Trennung; statisch reicht, da selten geändert; passt zum Muster der übrigen statischen Seiten |
| 2 | EN-Fassung | Volle EN-Übersetzung **mit Hinweis**, dass die LU-Fassung maßgeblich ist | Konsistent zur Zweisprachigkeit; die Verbindlichkeit bleibt bei der Landessprache |
| 3 | Impressum-Angaben | Name + E-Mail + **Postanschrift** + Partei + Inhaltsverantwortung | Betreiber wünscht vollständige, konventionelle Angabe |
| 4 | Textquelle | Datenschutz-Entwurf aus `docs/datenschutz.md` (durch mich), Impressum-Angaben vom Betreiber; finale Prüfung durch Fachperson | Faktentext ist ableitbar; Rechtsprüfung bleibt beim Menschen |
