# syntax=docker/dockerfile:1
#
# Mika Ferreira – Produktions-Image für Coolify.
#
# Drei aufbauende Stages, damit im Endbild weder Composer noch Node noch die
# Quellen der Build-Werkzeuge liegen:
#   vendor  – PHP-Abhängigkeiten (--no-dev)
#   assets  – Webpack Encore → public/build
#   runtime – FrankenPHP + App
#
# Kein Worker-Stage: Das Projekt hat keinen Messenger-Transport (kein
# symfony/messenger in composer.json), Mails gehen synchron über den
# Request raus. Anders als bei endlech gibt es hier also nur die Anwendung.
#
# FrankenPHP fährt im klassischen Request-pro-Prozess-Modell (kein Worker-Mode).

ARG PHP_VERSION=8.4
ARG FRANKENPHP_VERSION=1
ARG NODE_VERSION=24

# ---------------------------------------------------------------------------
# base – gemeinsame PHP-Grundlage für vendor- und runtime-Stage
# ---------------------------------------------------------------------------
FROM dunglas/frankenphp:${FRANKENPHP_VERSION}-php${PHP_VERSION} AS base

# Nur die Erweiterungen, die dieses Projekt wirklich braucht:
#   intl      – symfony/intl + twig/extra-bundle (Datums-/Zahlformate in der
#               zweisprachigen Ausgabe LU/EN)
#   pdo_mysql – MySQL 8 (Doctrine ORM, News + Admin)
#   opcache   – Pflicht in Produktion, sonst wird jede Datei je Request geparst
#
# ext-ctype und ext-iconv stehen zwar in composer.json (require), sind aber im
# offiziellen PHP-Image bereits eingebaut — und die Polyfills sind ohnehin per
# `replace` deaktiviert. Bewusst NICHT dabei: gd und zip (werden zur Laufzeit
# nicht gebraucht; zip bekommt Composer im vendor-Stage separat).
RUN install-php-extensions \
        intl \
        pdo_mysql \
        opcache

WORKDIR /app

# ---------------------------------------------------------------------------
# vendor – Composer-Abhängigkeiten
# ---------------------------------------------------------------------------
FROM base AS vendor

COPY --from=composer/composer:2-bin /composer /usr/bin/composer

# Nur hier, nicht im runtime-Stage: Composer entpackt die dist-Archive damit.
RUN install-php-extensions zip

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

# Erst nur die Manifeste kopieren: Solange sich composer.json/lock nicht ändern,
# bleibt dieser Layer im Cache – auch wenn jede andere Datei angefasst wurde.
COPY composer.json composer.lock symfony.lock ./

# --no-scripts ist hier Pflicht: `auto-scripts` in composer.json ruft
# `cache:clear`, `assets:install` und `importmap:install` — zu diesem Zeitpunkt
# gibt es aber weder src/ noch config/, und ohne prod-Umgebung würde cache:clear
# den falschen Cache bauen. Diese Schritte laufen bewusst erst im runtime-Stage.
# --no-autoloader, weil der Klassenmap erst nach dem Kopieren des Codes entsteht.
RUN --mount=type=cache,target=/tmp/composer-cache \
    COMPOSER_CACHE_DIR=/tmp/composer-cache \
    composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-progress

COPY . .

RUN composer dump-autoload --no-dev --classmap-authoritative

# ---------------------------------------------------------------------------
# assets – Webpack Encore
# ---------------------------------------------------------------------------
# ⚠ Das Projekt hat eine doppelte Asset-Pipeline (Encore UND AssetMapper, siehe
# docs/app-shell.md, Fehlbestand). Die tatsächlich ausgelieferte Optik kommt aus
# Encore: base.html.twig lädt `encore_entry_link_tags('app')` → public/build.
# Die AssetMapper-Hälfte (`importmap('app')`) wird im runtime-Stage kompiliert.
FROM node:${NODE_VERSION}-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./

# Anders als bei endlech gibt es hier KEINE file:vendor-Abhängigkeit
# (@symfony/ux-turbo kommt aus dem npm-Register), deshalb kein Vorabkopieren
# von vendor/ nötig.
RUN --mount=type=cache,target=/root/.npm \
    npm ci --no-audit --no-fund

# assets/, templates/ und src/ müssen vollständig vorliegen: assets/styles/app.css
# zieht Tailwind v4, das die verwendeten Klassen aus Templates und Quellcode
# scannt — fehlen sie, liefert der Build ein nahezu leeres Stylesheet aus, ohne
# dass er fehlschlägt.
COPY . .

RUN npm run build

# ---------------------------------------------------------------------------
# runtime – das Bild, das in Coolify läuft
# ---------------------------------------------------------------------------
FROM base AS runtime

ENV APP_ENV=prod \
    APP_DEBUG=0 \
    SERVER_NAME=":80"

# php.ini-production: display_errors=Off, kürzere Fehlerausgabe.
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# ⚠ `wget` allein für Coolifys Healthcheck. Coolify benutzt den HEALTHCHECK aus
# diesem Dockerfile NICHT — es setzt beim Ausrollen einen eigenen und ruft darin
# `wget` auf. Fehlt das Programm, scheitert die Prüfung, der frische Container
# gilt als krank und Coolify rollt auf den alten zurück. `curl` liegt zwar im
# Basisimage, hilft aber nicht: Coolifys Vorgabe fragt nach `wget`.
RUN apt-get update \
    && apt-get install -y --no-install-recommends wget \
    && rm -rf /var/lib/apt/lists/*

COPY <<'INI' /usr/local/etc/php/conf.d/zz-app.ini
; Produktionswerte für Symfony. `validate_timestamps=0` ist zulässig, weil ein
; Deploy hier immer ein neuer Container ist – es gibt keinen Fall, in dem sich
; eine PHP-Datei im laufenden Betrieb ändert.
opcache.enable = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 20000
opcache.validate_timestamps = 0

memory_limit = 256M

; Zeitzone der Anwendung.
date.timezone = Europe/Luxembourg

; Sitzungen (Admin-Login) liegen im Container, nicht in /tmp des Hosts.
session.cookie_httponly = 1
session.cookie_samesite = Lax
session.use_strict_mode = 1
INI

COPY --from=vendor  /app                   /app
COPY --from=assets  /app/public/build      /app/public/build

# var/ liegt nicht im Repo (gitignored) und muss deshalb hier entstehen.
# www-data ist der Benutzer, unter dem FrankenPHP die PHP-Threads fährt.
RUN set -eux; \
    mkdir -p var/cache var/log; \
    chown -R www-data:www-data var; \
    chmod -R 775 var

# Container aufwärmen. Die Platzhalterwerte gelten NUR für diese Build-Schritte:
# Symfony hält %env(...)% im kompilierten Container als Platzhalter und löst ihn
# erst zur Laufzeit auf — die echten Werte kommen aus Coolify. Kein Schritt
# verbindet sich mit der Datenbank.
#
#   importmap:install  – lädt die Vendor-JS (Stimulus/Turbo) nach assets/vendor/;
#                        gitignored, auf dem Coolify-Build sonst nicht vorhanden.
#   tailwind:build     – ⚠ Pflicht VOR asset-map:compile: das
#                        symfonycasts/tailwind-bundle lädt sein Tailwind-Binary
#                        (v4.1.11, siehe config/packages/symfonycasts_tailwind.yaml)
#                        und kompiliert assets/styles/app.css. Ohne diesen Schritt
#                        bricht asset-map:compile mit „Built Tailwind CSS file does
#                        not exist" ab.
#   asset-map:compile  – dumpt public/assets/, damit `importmap('app')` aus
#                        base.html.twig zur Laufzeit nicht ins Leere läuft.
#   assets:install     – legt public/bundles/ an (Bundle-Assets).
#   cache:warmup       – kompiliert den prod-Container, damit der erste echte
#                        Besucher nicht darauf wartet.
RUN set -eux; \
    export APP_SECRET=build-only; \
    php bin/console importmap:install --no-interaction; \
    php bin/console tailwind:build --minify --no-interaction; \
    php bin/console asset-map:compile --no-interaction; \
    php bin/console assets:install public --no-interaction; \
    php bin/console cache:warmup --no-interaction; \
    chown -R www-data:www-data var
# Nur var/ muss zur Laufzeit durch www-data beschreibbar sein (Cache, Log,
# Sessions). Alles unter public/ liest Caddy nur — dessen Eigentümer ist egal.

# Die Prüfung läuft über den PHP-Interpreter, der ohnehin da ist; ein
# Fehlerstatus lässt file_get_contents false zurückgeben, das ist das Signal.
# /health liefert schlicht „ok" ohne Datenbankzugriff (siehe HealthController).
#
# ⚠ Das gilt für `docker run` und Compose. **Coolify benutzt diese Zeile nicht** —
# es setzt beim Ausrollen einen eigenen Healthcheck (Vorgabe: GET /health mit
# `wget`, deshalb ist wget oben installiert).
HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD ["php", "-r", "$r = @file_get_contents('http://127.0.0.1/health'); exit(false !== $r && str_contains($r, 'ok') ? 0 : 1);"]

EXPOSE 80

# Caddys Zertifikats- und Konfigurationsspeicher gehören auf Volumes, nicht ins
# Image. In Coolify als Persistent Storage anlegen. Ein Uploads-Volume gibt es
# hier NICHT — die News-Entität speichert keine Dateien, das einzige Bild
# (public/images/mika.jpg) ist committet.
VOLUME ["/data", "/config"]

# ---------------------------------------------------------------------------
# app – MUSS das letzte Stage bleiben
# ---------------------------------------------------------------------------
# Reiner Alias auf `runtime`. Ohne `--target` baut Docker das LETZTE Stage einer
# Datei — und ein leer gelassenes „Docker build stage target" in Coolify tut
# dasselbe. Dieses Stage garantiert, dass dabei die Anwendung herauskommt.
FROM runtime AS app
