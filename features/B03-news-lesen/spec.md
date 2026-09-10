# B03 · Neuigkeiten lesen — Spezifikation

Status: `rekonstruiert` · Stand: 2026-09-10 · rückwärts aus dem Bestand erfasst

## Zweck

Besucher lesen die veröffentlichten Neuigkeiten — als Liste und als Einzelbeitrag, in der
gewählten Sprache (LB/EN). Keine Anmeldung nötig.

## Abhängigkeiten

| Braucht | Status | Warum |
|---|---|---|
| B02 Neuigkeiten verwalten | rekonstruiert | ohne gepflegte Beiträge gibt es nichts zu lesen |

## User Stories

- **US-01** · Als Besucher möchte ich die neuesten Beiträge in meiner Sprache lesen, um
  informiert zu bleiben.
- **US-02** · Als Besucher möchte ich einen einzelnen Beitrag vollständig lesen.

## Nicht im Scope

- Erstellen/Bearbeiten von Beiträgen (das ist B02).
- Kommentare, Teilen-Funktion, Suche (nicht gebaut).

## Akzeptanzkriterien

- **AK-01** · Angenommen, es gibt veröffentlichte Beiträge, wenn ich `/lb/news` öffne, dann
  sehe ich eine Liste absteigend nach Datum, je Beitrag Datum, Kategorie-Badge, Titel und
  Kurzfassung — auf Luxemburgisch.
- **AK-02** · Angenommen, ich öffne `/en/news`, dann sehe ich dieselbe Liste auf Englisch.
- **AK-03** · Angenommen, ein Beitrag hat ein Veröffentlichungsdatum in der Zukunft, wenn
  die öffentliche Liste geladen wird, dann erscheint er **nicht** (`published_at <= now`).
- **AK-04** · Angenommen, es gibt keine veröffentlichten Beiträge, wenn ich `/news` öffne,
  dann sehe ich den Leerzustand („news.empty").
- **AK-05** · Angenommen, ein gültiger Slug, wenn ich `/lb/news/{slug}` öffne, dann sehe
  ich den Einzelbeitrag mit Titel, Datum, Kategorie, Kurzfassung und Fließtext
  (Zeilenumbrüche bleiben erhalten, `whitespace-pre-line`).
- **AK-06** · Angenommen, ein unbekannter Slug, wenn ich `/news/{slug}` öffne, dann
  erhalte ich 404.
- **AK-07** ⚠ · Angenommen, ein Beitrag ist auf ein zukünftiges Datum gesetzt, wenn seine
  `{slug}`-URL direkt aufgerufen wird, dann **wird er angezeigt**.
  *(So verhält sich der Code heute — `NewsController::show` (`NewsController.php:25`) sucht
  nur nach `slug`, ohne Datumsfilter. Ein „geplanter" Beitrag ist damit über die direkte
  URL sichtbar, obwohl er in der Liste fehlt. Als Kriterium aufgenommen, damit `sdd-qa` es
  reproduziert; Einschätzung: unbeabsichtigtes Vorab-Leck, siehe OF-01 und FB-01.)*

### Datenschutz und Missbrauchsschutz

- **AK-08** · Angenommen, ein Beitragsinhalt enthält HTML/Script-Zeichen, wenn er
  angezeigt wird, dann wird er escaped ausgegeben (Twig-Autoescaping, **kein** `|raw`) —
  keine Stored-XSS über Beitragsinhalte.
- §1/§2/§3/§5: **trifft nicht zu** — öffentliche Inhalte, keine Besucherdaten, kein Login,
  keine Weitergabe an Dritte. §4 (Missbrauch/Kosten): trifft nicht zu, weil der Abruf
  kostenlos und öffentlich ist.

## Edge Cases

- **EC-01** · Kategorie außerhalb der vier bekannten → Kategorie-Badge zeigt einen
  fehlenden Übersetzungsschlüssel `home.themes.<category>.title`.
- **EC-02** · Slug existiert in keiner Sprache → 404 (identisch in `/lb` und `/en`).

## Fehlbestand

- **FB-01 · `show()` ohne Datumsfilter.** `NewsController.php:25` löst nur über `slug` auf.
  Folge: zukünftig datierte („geplante") Beiträge sind per Direkt-URL öffentlich abrufbar,
  wer den Slug kennt oder rät. Inkonsistent zur Liste (`findAllPublished`), die filtert.
- **FB-02 · Keine Paginierung.** `findAllPublished()` lädt alle Beiträge in eine Seite.
  Bei vielen Beiträgen wächst die Liste unbegrenzt. Fundstelle: `NewsRepository.php:22`.
- **FB-03 · Kein Canonical/hreflang** für die zwei Sprachfassungen desselben Beitrags
  (SEO) — gehört zu B06, hier vermerkt.

## Offene Fragen

- **OF-01** · Soll ein zukünftig datierter Beitrag auch über die Direkt-URL verborgen sein
  (404 bis zum Datum)? Einschätzung: ja — sonst ist „geplant" wirkungslos. Betreiber
  entscheidet.

## Decision Log

| # | Frage | Entscheidung | Begründung |
|---|---|---|---|
| 1 | Sichtbarkeitssteuerung | über `published_at <= now` in der Liste | kein eigenes Status-Feld nötig |
| 2 | Fließtext-Darstellung | `whitespace-pre-line` auf escaptem Text | einfache Absätze ohne Rich-Text/Markdown |
