# Docker Usage & Optional Features

This document covers advanced Docker usage, troubleshooting, and optional features for local development.

## Lando (Docker)

The runtime versions live in `.lando.yml` and are kept in step with
`.platform.app.yaml`: PHP 8.4, Node 24 and MariaDB 10.5. After changing any of
them run `lando rebuild -y`.

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
-   Run `lando artisan lumki:setup` (answer yes for every question)

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

## Notes

-   Some features (e.g., route caching) are not supported due to multilingual route extension. See main README for details.
