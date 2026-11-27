# syntax=docker/dockerfile:1.7

# The production image: FrankenPHP (Caddy with PHP built in) serving plain HTTP
# on port 8080 behind a TLS-terminating reverse proxy. One image runs every
# role - web, queue worker, migrations - chosen by the command
# (docker-entrypoint.sh). Nothing about a deployment is decided here: all
# configuration is environment at runtime, cached by the entrypoint at start,
# so the published image (.github/workflows/release-image.yaml) serves every
# deployment. See docs/docker.md, "Production image".

# Every PHP stage starts from this one image, pinned by digest so that a build
# is reproducible. Dependabot (.github/dependabot.yml) proposes the next digest
# each week, which is how the base image's security fixes arrive. PHP 8.4:
# composer.json allows ^8.4, and some locked packages stop at 8.5.
FROM dunglas/frankenphp:1-php8.4-trixie@sha256:61ea4bb945e3a85826e28df012cbcea3ef7f0d73c993f6c700b872e99c0f9f4b AS base

# The extensions the base lacks: pdo_mysql for MariaDB, intl (p3k/emoji-detector
# and Laravel's number formatting), pcntl so queue:work can enforce job
# timeouts and stop cleanly on SIGTERM. The rest composer.lock asks for
# (mbstring, sodium, curl, dom, ...) is in the base already; the composer
# stage below checks that with check-platform-reqs.
RUN install-php-extensions pdo_mysql intl pcntl

# ---------------------------------------------------------------- vendor ---
FROM base AS vendor

# Composer unpacks the dist archives with it; only this stage needs it.
RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2@sha256:5b2137f112f939a92788ceb5e55f54859d9dbf467a44037cf7760bdda949f365 \
    /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

WORKDIR /app
COPY composer.json composer.lock ./

# The lock as it is: install fails if it no longer matches composer.json.
# No scripts yet: they run artisan, which needs the application code.
RUN composer check-platform-reqs --no-dev \
    && composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-progress

COPY . .
RUN composer dump-autoload --no-dev --optimize --no-scripts

# ---------------------------------------------------------------- assets ---
# The Vite build. Node only exists in this stage; the image ships the built
# files in public/build.
FROM node:24-trixie-slim@sha256:4d00acb4bcc42c454c4eb801526f7ad7f772ea3bd4fd168c5cc2e384abcbb9d4 AS assets

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
# Tailwind scans Jetstream's and the paginator's Blade views for class names
# (tailwind.config.js), so they have to be here for the CSS to contain them.
COPY --from=vendor /app/vendor/laravel/jetstream ./vendor/laravel/jetstream
COPY --from=vendor /app/vendor/laravel/framework/src/Illuminate/Pagination/resources ./vendor/laravel/framework/src/Illuminate/Pagination/resources
RUN npm run build

# ---------------------------------------------------------------- runtime ---
FROM base AS runtime

# PHP's production defaults (no display_errors, no expose_php) plus ours.
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-dashboard.ini"
COPY docker/Caddyfile /etc/frankenphp/Caddyfile

# An unprivileged user. FrankenPHP ships with the capability to bind ports
# below 1024; this one listens on 8080 and runs with every capability
# dropped, where a binary carrying a file capability cannot even start.
RUN useradd --uid 5678 --user-group --no-create-home --home-dir /app app \
    && setcap -r /usr/local/bin/frankenphp \
    && mkdir -p /data/caddy /config/caddy \
    && chown -R app:app /data /config

WORKDIR /app
# Owned by root: the app may write only to storage and bootstrap/cache (below),
# so a bug that writes files cannot drop code into public/ or app/.
COPY --from=vendor /app /app
COPY --from=assets /app/public/build /app/public/build
COPY docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

# package:discover writes the provider manifest the --no-scripts install left
# out. It reads no configuration, so it can run here; the config, route, view
# and event caches depend on the environment and are built at start.
# storage:link serves storage/app/public (the self-hosted Google Fonts,
# config/google-fonts.php) at /storage.
RUN chmod 0755 /usr/local/bin/docker-entrypoint.sh \
    && mkdir -p storage/app/public storage/framework/cache/data \
        storage/framework/sessions storage/framework/views storage/logs \
        bootstrap/cache \
    && rm -f .env bootstrap/cache/*.php \
    && php artisan package:discover --ansi \
    && php artisan storage:link \
    && chown -R app:app storage bootstrap/cache

USER app

# Baked in so the running revision can be read off the container.
ARG GIT_REVISION=""
ENV GIT_REVISION=${GIT_REVISION}

ENV PORT=8080 \
    LOG_CHANNEL=stderr \
    APP_ENV=production \
    APP_DEBUG=false

# /up boots the application and asks the database (routes/health.php). The
# X-Forwarded-Proto header stands in for the proxy: every plain-HTTP request
# is otherwise redirected to https (App\Http\Middleware\HttpsProtocol). Only
# meaningful for the web role; the compose file turns it off for the others.
HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 \
    CMD curl -fsS -o /dev/null -H 'X-Forwarded-Proto: https' "http://127.0.0.1:${PORT}/up" || exit 1

EXPOSE 8080

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["web"]
