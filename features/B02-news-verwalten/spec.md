# B02 · Neuigkeiten verwalten — Spezifikation

Status: `rekonstruiert` · Stand: 2026-09-10 · rückwärts aus dem Bestand erfasst

## Zweck

Der angemeldete Betreiber legt zweisprachige Neuigkeiten an, bearbeitet und löscht sie —
ohne Entwickler. Beide Sprachfassungen (LB/EN) werden in einem Formular gepflegt.

## Abhängigkeiten

| Braucht | Status | Warum |
|---|---|---|
| B01 Admin-Login | rekonstruiert | ohne Anmeldung kein Zugang zu `/admin` |

## User Stories

- **US-01** · Als Betreiber möchte ich einen neuen Beitrag in beiden Sprachen anlegen,
  damit Besucher aktuelle Neuigkeiten lesen.
- **US-02** · Als Betreiber möchte ich einen Beitrag bearbeiten oder löschen, damit
  veraltete oder falsche Inhalte verschwinden.

## Nicht im Scope

- Öffentliche Anzeige der Beiträge (das ist B03).
- Kategorienverwaltung — die vier Kategorien sind fest im Formular hinterlegt.

## Akzeptanzkriterien

- **AK-01** · Angenommen, ich bin als Admin angemeldet, wenn ich `/admin` öffne, dann sehe
  ich eine Tabelle aller Beiträge (auch zukünftig datierter), absteigend nach
  Veröffentlichungsdatum, mit Titel (LB/EN), Kategorie, Datum und Aktionen.
- **AK-02** · Angenommen, alle Pflichtfelder sind ausgefüllt (Titel/Kurzfassung/Inhalt je
  Sprache, Kategorie, Slug, Datum), wenn ich „Neien Artikel" abschicke, dann wird der
  Beitrag gespeichert, es erscheint „Artikel ugeluecht." und ich lande wieder im Dashboard.
- **AK-03** · Angenommen, ein Pflichtfeld ist leer, wenn ich abschicke, dann erscheint ein
  Validierungsfehler am Feld und es wird nicht gespeichert.
- **AK-04** · Angenommen, ein Beitrag existiert, wenn ich ihn über „Ännern" ändere und
  speichere, dann ist die Änderung persistiert und es erscheint „Artikel aktualiséiert."
- **AK-05** · Angenommen, ein Beitrag existiert, wenn ich das Löschen mit gültigem
  CSRF-Token bestätige, dann ist der Beitrag entfernt und es erscheint „Artikel geläscht."
- **AK-06** · Angenommen, ich gebe eine nicht existierende `{id}` an, wenn ich
  `/admin/news/{id}/edit` oder `/delete` aufrufe, dann erhalte ich 404.
- **AK-07** · Angenommen, ich bin die englische Titelzeile am Tippen und der Slug ist leer,
  dann wird der Slug automatisch aus dem englischen Titel erzeugt (clientseitig), solange
  ich ihn nicht selbst bearbeite.

### Datenschutz und Missbrauchsschutz

- **AK-08** · Angenommen, ich bin nicht angemeldet, wenn ich eine `/admin/news/*`-Route
  aufrufe, dann werde ich auf `/login` geleitet (Absicherung allein über `access_control
  ^/admin`; die Controller tragen kein zusätzliches `#[IsGranted]`).
- **AK-09** · Angenommen, ein Lösch-POST trägt kein gültiges Token `delete-{id}`, wenn er
  eintrifft, dann wird **nicht** gelöscht (stille Rückleitung zum Dashboard).
- **AK-10** · Angenommen, ein Anlege-/Bearbeiten-Formular wird ohne gültiges CSRF-Token
  abgeschickt, dann wird es abgelehnt (Symfony-Form-CSRF, standardmäßig aktiv).
- §1/§2/§5: **trifft nicht zu** — Beitragsinhalte sind öffentliche redaktionelle Texte,
  keine personenbezogenen Besucherdaten; keine Weitergabe an Dritte.

## Edge Cases

- **EC-01** · Kategorie außerhalb der vier Auswahlwerte ist über das Formular nicht
  wählbar; auf DB-Ebene aber möglich (siehe Fehlbestand / Datenmodell) → im öffentlichen
  Template fehlt dann die Kategorie-Übersetzung.
- **EC-02** · Zukünftiges `publishedAt` ist erlaubt; der Beitrag erscheint im Dashboard,
  aber (in der Liste) nicht öffentlich — der einzige „geplant"-Mechanismus. Beabsichtigt.

## Fehlbestand

- **FB-01 · Slug ohne Eindeutigkeitsprüfung im Formular.** `NewsType` setzt auf `slug` nur
  `NotBlank`; die DB hat einen Unique-Index. Folge: ein doppelter Slug führt zu einer
  Doctrine-`UniqueConstraintViolation` → **500**, statt zu einem Feldfehler. Ein
  `UniqueEntity`-Constraint fehlt. Fundstelle: `Form/NewsType.php:58`, `Entity/News.php:38`.
- **FB-02 · `category` auf DB-Ebene unbeschränkt** (VARCHAR(100), kein Enum/CHECK). Nur das
  Formular begrenzt auf vier Werte. Folge: künftiger Code oder Direktzugriff kann eine
  unbekannte Kategorie setzen → fehlende Übersetzung im Template.
- **FB-03 · Kein Objekt-Voter.** Jeder Admin darf jeden Beitrag ändern/löschen (kein
  Autor-Bezug). Bei einem Betreiber unkritisch; bei mehreren Redakteuren eine Lücke.
- **FB-04 · Inline-`<script>` im News-Formular** (Slug-Generierung, Sprach-Reiter) statt
  eines Stimulus-Controllers; läuft ohne CSP-Nonce. Fundstelle:
  `templates/admin/news_form.html.twig:118`.
- **FB-05 · Kein `updated_at`/`created_at`** — Bearbeitungen sind nicht nachvollziehbar
  (siehe Datenmodell).

## Offene Fragen

- **OF-01** · Soll ein doppelter Slug als Feldfehler statt 500 abgefangen werden (FB-01)?
  Empfehlung: `UniqueEntity` ergänzen.

## Decision Log

| # | Frage | Entscheidung | Begründung |
|---|---|---|---|
| 1 | Zweisprachigkeit im Datenmodell | zwei Spaltensätze in einer Zeile | einfaches Pflegen beider Sprachen in einem Formular |
| 2 | CSRF beim Löschen | manuelles `isCsrfTokenValid('delete-'~id)` | Löschen ist ein einfaches `<form>`, kein Symfony-Formular |
| 3 | Slug sprachübergreifend, englisch-basiert | ein Slug für beide Sprachen | stabile, sprachneutrale URL |
