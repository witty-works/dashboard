#!/bin/sh
set -eu

# One image, three roles, picked by the first argument:
#
#   web      FrankenPHP on $PORT (default)
#   worker   the database queue worker
#   migrate  php artisan migrate --force, then exit
#
# Anything else runs as given, e.g. `php artisan superadmin:create ...`.
# See docs/docker.md, "Production image".

role="${1:-web}"

# Laravel reads configuration from the environment only until it is cached,
# and the cache has to be built here rather than in the image: the values are
# the deployment's. A setting read with env() outside config/ would be null
# from here on, so keep env() calls in config/.
cache_framework() {
    if [ -z "${APP_KEY:-}" ]; then
        echo "APP_KEY is not set; generate one with: php artisan key:generate --show" >&2
        exit 1
    fi
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
}

case "$role" in
    web)
        cache_framework
        # The fonts are served from here rather than from Google
        # (config/google-fonts.php). Their CSS carries absolute URLs under
        # APP_URL, so they are fetched now rather than at build time. A
        # failure is not fatal: the first page that needs a font fetches it.
        php artisan google-fonts:fetch || echo "google-fonts:fetch failed; fonts are fetched on first use" >&2
        exec frankenphp run --config /etc/frankenphp/Caddyfile --adapter caddyfile
        ;;
    worker)
        cache_framework
        # Queues in priority order: the jobs dispatch to high, medium and low.
        # --tries and --backoff apply to jobs without retry settings of their
        # own; the NLP API and PostHog jobs retry for a day with a backoff
        # growing to an hour (App\Jobs\Concerns\RetriesThroughOutages). --max-time ends the worker every hour,
        # and the container's restart policy starts a fresh one, which bounds
        # memory growth. SIGTERM lets the current job finish first.
        exec php artisan queue:work database \
            --queue="${QUEUE_NAMES:-high,medium,low,default}" \
            --tries="${QUEUE_TRIES:-3}" \
            --backoff="${QUEUE_BACKOFF:-10}" \
            --timeout="${QUEUE_TIMEOUT:-120}" \
            --max-time="${QUEUE_MAX_TIME:-3600}" \
            --sleep=3
        ;;
    migrate)
        cache_framework
        exec php artisan migrate --force
        ;;
    *)
        exec "$@"
        ;;
esac
