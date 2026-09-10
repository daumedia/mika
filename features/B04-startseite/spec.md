# B04 · Startseite — Spezifikation

Status: `rekonstruiert` · Stand: 2026-09-10 · rückwärts aus dem Bestand erfasst

## Zweck

Die Startseite fasst die Person, ihre vier politischen Themen und die neuesten
Neuigkeiten auf einer scrollbaren Seite zusammen — je Sprache. Sie ist die Einstiegs- und
Visitenkarte der Website.

## Abhängigkeiten

| Braucht | Status | Warum |
|---|---|---|
| B03 Neuigkeiten lesen | rekonstruiert | die News-Vorschau verlinkt in die Detailansicht |
| B06 Zweisprachigkeit | rekonstruiert | Seite existiert je Sprache (`/lb`, `/en`) |

## User Stories

- **US-01** · Als Besucher möchte ich auf einen Blick erfassen, wer die Person ist und
  wofür sie steht.
- **US-02** · Als Besucher möchte ich die neuesten Beiträge sehen und weiterlesen können.

## Nicht im Scope

- Vollständige Beitragsanzeige (das ist B03) und das Kontaktformular (bewusst keins, B05).

## Akzeptanzkriterien

- **AK-01** · Angenommen, ich öffne `/lb`, dann sehe ich nacheinander: Hero (Name, Badge,
  Tagline, CTAs, Porträt), Kurzporträt mit drei Werten, vier Themenkarten (Inklusion,
  Jugend, Wohnen, Kultur), eine News-Vorschau und einen Kontaktblock — auf Luxemburgisch.
- **AK-02** · Angenommen, ich öffne `/en`, dann sehe ich dieselbe Seite auf Englisch.
- **AK-03** · Angenommen, es gibt mindestens einen veröffentlichten Beitrag, wenn die
  Startseite lädt, dann zeigt die News-Vorschau bis zu **drei** neueste Beiträge; gibt es
  keinen, wird der Vorschaublock ausgeblendet.
- **AK-04** · Angenommen, ich klicke einen Vorschau-Beitrag, dann öffnet sich dessen
  Detailseite im aktuellen Locale.
- **AK-05** · Angenommen, ich klicke einen Anker-CTA (`#themes`, `#contact`), dann scrollt
  die Seite sanft zum jeweiligen Abschnitt (`scroll-behavior: smooth`, außer bei
  `prefers-reduced-motion`).

### Datenschutz und Missbrauchsschutz

- **AK-06** · Angenommen, die Seite lädt, dann werden die Schriften Fraunces/Figtree von
  Google-Servern nachgeladen (überträgt Besucher-IP an Google). *(Querschnittlich, siehe
  PRD OF-03 — lokale Auslieferung erwägen.)*
- §1/§2(außer Fonts)/§3/§5: **trifft nicht zu** — öffentliche Darstellung, keine
  Besucherdaten, keine Eingaben.

## Edge Cases

- **EC-01** · Fehlt das Porträt `images/mika.jpg`, bricht das Bild ohne Fallback.
- **EC-02** · Anker-CTAs (`#themes`, `#contact`) wirken nur auf der Startseite selbst.

## Fehlbestand

- **FB-01 · `|raw` auf Übersetzungsstrings** (`home.hero.tagline`, `home.about.headline`).
  Aktuell entwicklerkontrolliert (Übersetzungsdateien) → **kein aktueller XSS**. Folge:
  Wer künftig Übersetzungen pflegen darf, kann HTML/Script einschleusen. Fundstelle:
  `templates/home/index.html.twig:33,79`.
- **FB-02 · Porträt ohne Fallback/`loading`-Attribut** — fehlendes Bild bleibt leer; kein
  `loading="lazy"`. Gering.

## Offene Fragen

- **OF-01** · Google Fonts lokal ausliefern (AK-06 / PRD OF-03)? Empfehlung: ja.

## Decision Log

| # | Frage | Entscheidung | Begründung |
|---|---|---|---|
| 1 | Aufbau | eine Scroll-Seite mit Abschnitten + separate Unterseiten | typische Landingpage-Struktur |
| 2 | News-Vorschau-Anzahl | drei (`findLatest(3)`) | kompakte Vorschau |
| 3 | reichhaltige Tagline/Headline | `|raw` auf Übersetzung | erlaubt Formatierung — mit Risiko (FB-01) |
