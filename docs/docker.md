# Docker Usage & Optional Features

This document covers advanced Docker usage, troubleshooting, and optional features for local development, and the production image that deployments run ([Production image](#production-image)).

## Lando (Docker)

The runtime versions live in `.lando.yml`: PHP 8.4, Node 24 and MariaDB 10.5.
After changing any of them run `lando rebuild -y`.

-   Install Lando: https://docs.lando.dev/basics/installation.html
-   On Windows 10 use WSL2: https://blog.calevans.com/2020/06/18/making-lando-work-inside-wsl2/
-   Start the environment:
    ```bash
    lando start
    lando composer install
    lando artisan migrate
    lando artisan storage:link
    lando npm install --save
    lando npm run dev
    ```
-   Install the Lando certificate in your browser: https://docs.lando.dev/config/security.html#certificates
-   Go to the website and create an account
-   Run `lando artisan superadmin:create you@example.com "Your Name"` to get a superadmin account

## Switching between branches

`vendor/`, `node_modules/`, `storage/framework/views/` and `bootstrap/cache/`
are all gitignored, so they survive a `git checkout` and keep whatever versions
the *previous* branch installed. Branches that differ in Laravel, Livewire or
Vite therefore break in confusing ways until those are resynced:

```bash
lando composer install                 # vendor to this branch's lock
lando npm ci                           # node_modules to this branch's lock
lando artisan view:clear               # compiled Blade
rm -f bootstrap/cache/*.php            # cached package manifest
lando artisan package:discover
```

Two failures that look unrelated but are always this:

-   `Class "Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys"
    not found` — Blade compiled under a newer Livewire than the one installed.
    Fixed by `view:clear`.
-   `Class "Barryvdh\Debugbar\ServiceProvider" not found` — `bootstrap/cache`
    still lists the old debugbar provider; v4 moved to
    `Fruitcake\LaravelDebugbar`. Fixed by clearing the cache and re-discovering.

Install node modules **inside Lando** (`lando npm ci`), not on the host. Vite's
Rolldown ships platform-specific native bindings, so a host install on macOS
leaves the Linux container without `@rolldown/binding-linux-*` and the build
dies.

## Troubleshooting

-   If you encounter issues with Docker containers, check Lando logs and restart containers as needed.
-   For advanced configuration, see the [Lando documentation](https://docs.lando.dev/config/).

## Optional Features

-   Local test emails: http://mail.lndo.site/
-   Sentry CLI integration: See [docs/sentry.md](sentry.md)

## Production image

`Dockerfile` builds the image deployments run, and `.github/workflows/release-image.yaml` publishes it to `ghcr.io/witty-works/dashboard` for every version tag (`1.43.0`, and `1.43.0-rc1` for a release candidate). Each release is tagged with its full version and with its minor line (`1.43`); there is no `latest`. The run summary prints the digest: pin deployments by digest. `.github/workflows/image.yaml` builds the same image without pushing it on any branch that changes the Dockerfile, `docker/`, the lockfiles or the asset build, and checks that it migrates a fresh MariaDB and serves `/up` and the login page.

The image holds no deployment configuration. Everything is environment at runtime, as in `.env.example`; the entrypoint caches it (`config:cache`, `route:cache`, `view:cache`, `event:cache`) when the container starts. Read settings with `config()`: `env()` outside `config/` returns `null` once the configuration is cached.

### Roles

One image, with the role as the command (`docker/docker-entrypoint.sh`):

| Command | Runs |
| --- | --- |
| `web` (default) | FrankenPHP, plain HTTP on `PORT` (8080) |
| `worker` | `queue:work database` on the queues `high,medium,low,default` |
| `migrate` | `migrate --force`, then exits |
| anything else | as given, e.g. `php artisan superadmin:create you@example.com "Your Name"` |

The worker's flags come from `QUEUE_TRIES` (3, for jobs that set no `$tries` of their own; the NLP API and PostHog jobs do), `QUEUE_BACKOFF` (10 seconds, likewise), `QUEUE_TIMEOUT` (120 seconds per job) and `QUEUE_MAX_TIME` (3600). After `QUEUE_MAX_TIME` the worker exits, and the container's restart policy starts a fresh one.

There is no scheduler role: `App\Console\Kernel` schedules nothing. The commands a deployment may want to run regularly are `nlp_api:sync` (resends every user and team config to the NLP API), `kpis`, `writing-streak-kpi`, `posthog:sync` and `statistics`; run them from the host's scheduler with the image, as `php artisan <command>`.

### Web server

FrankenPHP (Caddy with PHP built in) in classic mode, from `dunglas/frankenphp`, PHP 8.4. It serves plain HTTP on port 8080 as an unprivileged user and expects a reverse proxy in front that terminates TLS and sets `X-Forwarded-Proto`, `X-Forwarded-Host` and `X-Forwarded-For`: every request without `X-Forwarded-Proto: https` is redirected to https (`App\Http\Middleware\HttpsProtocol`), and `TrustProxies` trusts any proxy, so the container must not be reachable except through it.

`GET /up` answers `200 OK` when the application boots and the database answers, and `503` otherwise (`routes/health.php`). It opens no session. The image's `HEALTHCHECK` calls it; only the web role serves it, so turn the health check off for the others.

### Configuration a deployment sets

Besides the settings in `.env.example` and the README's "Custom Application Configuration":

| Setting | Value in production |
| --- | --- |
| `APP_KEY` | `php artisan key:generate --show`; the container refuses to start without it |
| `APP_URL` | the public address, `https://…`; also the OAuth issuer (see [oauth.md](oauth.md#issuer)) |
| `PASSPORT_PRIVATE_KEY`, `PASSPORT_PUBLIC_KEY` | the PEM keys from `php artisan passport:keys`, newlines written as `\n`; replacing them invalidates every issued token |
| `QUEUE_CONNECTION` | `database` (the default `sync` runs jobs inside the request) |
| `SESSION_DRIVER` | `database` |
| `LOG_CHANNEL` | `stderr` (the image's default), so the container log collects it |
| `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS` | SMTP; port 587 uses STARTTLS, 465 TLS |

The cache stays on the default file store inside the container: it holds the OAuth signing key's JWK, PostHog insights and rate-limit counters, all of which can be rebuilt, so a new container starting empty costs nothing.

### State

The containers keep nothing that has to survive them. Sessions, the queue and failed jobs are in the database. Logs go to stderr. `storage/app/public` holds the Google Fonts the web role fetches at start (`config/google-fonts.php`), served at `/storage`; their CSS carries absolute URLs under `APP_URL`, so they are not fetched at build time. `storage/app/data` holds category tables that are part of the code, so no volume may be mounted over `storage/app`.

### First start

1.  Start the database and run `migrate`.
2.  `php artisan superadmin:create you@example.com "Your Name"`; it mails a link to set the password.
3.  `php artisan passport:editor-client` for the write page, then set `PASSPORT_EDITOR_CLIENT_ID` and `PASSPORT_FIRST_PARTY_CLIENTS` to the id it prints, and add the id to the NLP API's `DASHBOARD_CLIENT_ID` ([oauth.md](oauth.md#the-write-pages-flow)). `passport:extension-client` likewise, if the browser extension signs in to this deployment.

### Building locally

```bash
docker build -t dashboard:local .
```

Deployments run `linux/amd64`; on an ARM Mac add `--platform linux/amd64` to build what they run.

## Notes

-   Route caching works with the localization package in use (`niels-numbers/laravel-localizer`); the production image caches routes at start.
