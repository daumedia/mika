# B06 · Zweisprachigkeit (LB/EN) — Spezifikation

Status: `rekonstruiert` · Stand: 2026-09-10 · rückwärts aus dem Bestand erfasst

## Zweck

Jede öffentliche Seite ist auf Luxemburgisch (Standard) und Englisch erreichbar. Besucher
schalten über einen Umschalter in der Kopfzeile zwischen den Sprachen um; die URL trägt
das Sprachkürzel.

## Abhängigkeiten

| Braucht | Status | Warum |
|---|---|---|
| — | — | querschnittlich; alle öffentlichen Features hängen daran |

## User Stories

- **US-01** · Als Besucher möchte ich die Seite in meiner Sprache lesen und jederzeit
  umschalten können.

## Nicht im Scope

- Übersetzung des Admin-Bereichs (bewusst nur Luxemburgisch, siehe AK-06).
- Weitere Sprachen über LB/EN hinaus.

## Akzeptanzkriterien

- **AK-01** · Angenommen, ich bin auf einer Seite unter `/lb`, wenn ich im Umschalter „EN"
  klicke, dann lande ich auf derselben Seite unter `/en` mit englischen Texten.
- **AK-02** · Angenommen, ich bin auf `/lb/news/{slug}`, wenn ich umschalte, dann lande ich
  auf `/en/news/{slug}` mit **demselben** Slug (Route-Parameter bleiben erhalten).
- **AK-03** · Angenommen, ich öffne `/` ohne Sprachpräfix, dann werde ich per **301** auf
  `/lb` weitergeleitet.
- **AK-04** · Angenommen, eine Seite wird ohne expliziten `_locale` geladen, dann gilt
  Luxemburgisch als Standard (`LocaleSubscriber`, `defaultLocale = 'lb'`).
- **AK-05** · Angenommen, die aktuelle Route steht nicht in der `route_map` des Umschalters,
  wenn ich umschalte, dann lande ich auf der Startseite der Zielsprache (Fallback).
- **AK-06** · Angenommen, ich bin im Admin-Bereich, dann sind die Oberflächentexte immer
  Luxemburgisch, unabhängig von der zuvor gewählten Sprache. *(Bewusst — Admin ist nicht
  übersetzt.)*

### Datenschutz und Missbrauchsschutz

- §1–§6: **trifft nicht zu** — reine Routing-/Darstellungslogik ohne Daten, ohne Login,
  ohne externe Dienste.

## Edge Cases

- **EC-01** · `<html lang>` folgt dem aktuellen Locale.
- **EC-02** · Der Umschalter ist in Kopfzeile (Desktop) und Mobilmenü **doppelt** gepflegt
  (siehe Fehlbestand FB-03).

## Fehlbestand

- **FB-01 · Keine `hreflang`-Alternates** im `<head>`. Die zwei Sprachfassungen sind für
  Suchmaschinen nicht als Alternativen ausgezeichnet. Folge: schwächere SEO, mögliche
  Duplicate-Content-Einstufung. Fundstelle: `templates/base.html.twig` (head).
- **FB-02 · Sprachwahl wird nicht gemerkt.** Kein Cookie, kein `Accept-Language`-Handling;
  `/` geht immer nach `/lb`. Ein englischsprachiger Besucher landet zuerst auf
  Luxemburgisch. Möglicherweise bewusst (Standard = Landessprache), als Verhalten notiert.
- **FB-03 · `route_map` doppelt im Template** (Desktop + Mobilmenü in `base.html.twig`).
  Folge: Wartungsrisiko — neue Routen müssen an zwei Stellen ergänzt werden, sonst driftet
  der Umschalter.
- **FB-04 · Zwei vollständige Routensätze je Controller** (`app_*` und `app_*_en`) statt
  eines `{_locale}`-Präfix-Imports. Mehr Routen, aber explizit. Als Struktur notiert.

## Offene Fragen

- **OF-01** · Soll `hreflang` ergänzt werden (FB-01)? Empfehlung: ja, vor Prod.

## Decision Log

| # | Frage | Entscheidung | Begründung |
|---|---|---|---|
| 1 | Sprachsteuerung | URL-Präfix `/lb` `/en` | teilbare, sprachexplizite URLs; kein Cookie nötig |
| 2 | Standardsprache | `lb` | Landessprache der Gemeinde |
| 3 | Admin übersetzen? | nein, nur lb | einziger Nutzer ist der Betreiber |
