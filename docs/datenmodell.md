# Datenmodell — Mika Ferreira

Stand: 2026-09-10 · aus `migrations/` und `src/Entity/` rückwärts erfasst.

Zwei Tabellen, **keine Beziehung** zwischen ihnen. MySQL 8, InnoDB,
`utf8mb4_unicode_ci`. Doctrine besitzt das Schema allein.

## Entität `News` — Tabelle `news`

Zweisprachige Neuigkeit. Beide Sprachen liegen als getrennte Spalten in **einer** Zeile;
es gibt keinen Übersetzungs-Join.

| Feld (Entity) | Spalte | Typ | Null | Bemerkung |
|---|---|---|---|---|
| `id` | `id` | INT, AUTO_INCREMENT | nein | Primärschlüssel |
| `titleLb` | `title_lb` | VARCHAR(255) | nein | Titel Luxemburgisch |
| `titleEn` | `title_en` | VARCHAR(255) | nein | Titel Englisch |
| `summaryLb` | `summary_lb` | LONGTEXT | nein | Kurzfassung LB |
| `summaryEn` | `summary_en` | LONGTEXT | nein | Kurzfassung EN |
| `contentLb` | `content_lb` | LONGTEXT | nein | Fließtext LB |
| `contentEn` | `content_en` | LONGTEXT | nein | Fließtext EN |
| `category` | `category` | VARCHAR(100) | nein | Backed-Enum `App\Enum\NewsCategory` (`enumType`), Werte `inclusion`/`youth`/`housing`/`culture` — Spaltentyp bleibt VARCHAR |
| `slug` | `slug` | VARCHAR(255) | nein | **unique** (`UNIQ_1DD39950989D9B62`), sprachübergreifend, englisch-basiert |
| `publishedAt` | `published_at` | DATETIME | nein | als `datetime_immutable` gemappt |

**Indizes:** Primärschlüssel `id`; Unique-Index auf `slug`.

**Zugriffslogik (Repository):**
- `findAllPublished()` und `findLatest(int $limit)` filtern `published_at <= NOW` und sortieren
  absteigend. Ein Beitrag mit Datum in der Zukunft ist damit öffentlich **unsichtbar**,
  im Admin-Dashboard (`findBy([], ['publishedAt' => 'DESC'])`) aber **sichtbar**. Das ist
  der einzige „Entwurf/geplant"-Mechanismus — es gibt kein eigenes Status-Feld.
- Öffentliche Auflösung erfolgt über `slug` (`findOneBy(['slug' => …])`); fehlender Slug → 404.

**Lösch­regel:** hart. `AdminController::deleteNews()` ruft `EntityManager::remove()` +
`flush()`, kein Soft-Delete, kein Papierkorb. Da keine Fremdschlüssel auf `news` zeigen,
entstehen keine verwaisten Zeilen.

## Entität `Admin` — Tabelle `admin`

Der einzige Anmeldungs­typ. Repräsentiert den Betreiber, **nicht** Besucher.

| Feld (Entity) | Spalte | Typ | Null | Bemerkung |
|---|---|---|---|---|
| `id` | `id` | INT, AUTO_INCREMENT | nein | Primärschlüssel |
| `username` | `username` | VARCHAR(180) | nein | **unique** (`UNIQ_880E0D76F85E0677`), Login-Kennung |
| `password` | `password` | VARCHAR(255) | nein | Hash (Symfony `auto`-Hasher, aktuell bcrypt/argon) |
| `roles` | `roles` | JSON | nein | zusätzliche Rollen; leer im Normalfall |

`getRoles()` hängt **immer** `ROLE_ADMIN` an, unabhängig vom gespeicherten Array. Es gibt
faktisch nur eine Rolle. `eraseCredentials()` ist leer (kein Klartext­passwort im Objekt).

**Provisionierung:** über `CreateAdminCommand` (CLI) — der produktionssichere Weg. Die
`AdminFixtures` legen zusätzlich einen Admin `mika` mit Passwort `admin` an (nur für
lokale Entwicklung gedacht).

**Löschregel:** keine Selbst-Löschung, kein Konto-Management über die Oberfläche.

## Beziehungen

Keine. `news` trägt **keinen** Fremdschlüssel auf `admin` — der Autor eines Beitrags wird
nicht gespeichert. Bei einem einzigen Betreiber ist das vertretbar.

## Fehlbestand

Lücken, die als Befund festzuhalten sind — **kein** stillschweigendes Zurechtrücken:

- ~~**`category` ist auf DB-Ebene unbeschränkt** (VARCHAR(100), kein CHECK/Enum)~~ ✅
  behoben 2026-09-11 (BF-05): `category` ist jetzt ein PHP-Backed-Enum
  `App\Enum\NewsCategory` (`enumType` am Mapping), die vier Werte (`inclusion`, `youth`,
  `housing`, `culture`) sind im Code typgesichert. Der Spaltentyp bleibt VARCHAR(100) — die
  Backing-Values werden gespeichert, es war keine Migration nötig. Ein ungültiger Wert
  scheitert nun schon bei der Hydration, nicht erst am fehlenden Übersetzungsschlüssel.
- **Kein `created_at` / `updated_at`.** Nur `published_at`, das der Redakteur frei setzt.
  Anlage- und Änderungszeitpunkt sind nicht nachvollziehbar.
- **`published_at` ist ein naives DATETIME** ohne Zeitzone; verglichen wird gegen
  `new \DateTimeImmutable()` in der Serverzeit. Bei abweichender DB-/App-Zeitzone kann die
  Sichtbarkeitsgrenze um Stunden verrutschen. → Betriebsthema.
- **Autor nicht erfasst** (siehe Beziehungen) — relevant, sobald es je mehr als einen
  Redakteur gibt.
