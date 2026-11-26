# Introduction

Dashboard for Witty

Handles registration, SSO, user license management, organization rules and
personal/team statistics by integrating with https://github.com/witty-works/nlp_api as well as https://github.com/witty-works/browser-extension and https://github.com/witty-works/word-plugin

Based on

-   https://jetstream.laravel.com/
-   https://laravel.com/docs/socialite

Runs on Laravel 13 / PHP 8.4, with Node 24 for the asset build.

# Local Install

-   Clone Code

    -   Make sure to add your SSH key to github
    -   Run `git clone git@github.com:witty-works/dashboard.git`
    -   Get a current env file from a co-worker or `cp .env.example .env` (might
        need to set some API keys in order for the next steps to work)
    -   Install Lando https://docs.lando.dev/basics/installation.html
        -   On Windows 10 use WSL2
            https://blog.calevans.com/2020/06/18/making-lando-work-inside-wsl2/
    -   Run `lando start`
    -   Run `lando composer install`
    -   Run `lando artisan migrate`
    -   Run `lando artisan storage:link`
    -   Run `lando npm install --save`
    -   Run `lando npm run dev`
    -   Install the lando certificate in your browser
        -   https://docs.lando.dev/config/security.html#certificates
    -   Go to the website and create an account
    -   Run `lando artisan superadmin:create you@example.com "Your Name"` to get a superadmin account

-   Install Sentry CLI https://docs.sentry.io/product/cli/installation/
    -   Run `sentry-cli login`
    -   Run `cp .sentryclirc.example .sentryclirc`
    -   Edit `.sentryclirc` to add the auth token from
        https://sentry.io/settings/account/api/auth-tokens/

**Note:** Plain `php artisan route:cache` is _not supported_ by the multilingual
route extension used in this project and will break route localization. Use
`php artisan route:trans:cache`, which builds one cache per locale — run it on
deploy, after the code is in place. `route:trans:clear` removes them again.

# Development

See [docs/docker.md](docs/docker.md) for Docker/Lando development workflow.
Go to `https://dashboard.lndo.site`

Use gitflow to do feature or hotfix branches:
https://nvie.com/posts/a-successful-git-branching-model/

See here for installation instructions:
https://github.com/nvie/gitflow/wiki/Installation

# Testing

The suite covers the critical paths (guest pages, login, registration, the signed-in
pages, the `/admin` permission gate, Livewire mounting and localization). It exists
mainly as a safety net for framework upgrades.

Tests run against a real MariaDB schema, not SQLite: the migration history uses
MySQL-specific DDL that SQLite cannot replay. `phpunit.xml` pins only the database
_name_ (`testing`), so a run can never touch your development database.

Create the database once:

```
lando mysql -uroot -e "CREATE DATABASE IF NOT EXISTS testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; GRANT ALL ON testing.* TO 'laravel'@'%';"
```

Then run the suite inside Lando, where the connection details in `.env` already apply:

```
lando php vendor/bin/phpunit
```

To run it from the host instead, point it at Lando's forwarded MariaDB port:

```
DB_HOST=127.0.0.1 DB_PORT=3307 vendor/bin/phpunit
```

The OAuth tests need Passport's signing keys; create them once with `php artisan passport:keys`.

CI (`.github/workflows/ci.yaml`) runs the suite on every pull request and on `dev` and `main`, against a MariaDB 10.5 service, and runs `npm test` and the Vite build. The tests fake the Google Fonts request and skip Vite, so they need neither the network nor a build.

Two things to know when writing tests:

-   **Assert the view as well as the status.** Use `assertViewIs(...)` alongside
    `assertOk()` so a test cannot pass against the fallback error page.
-   **Call `$this->withoutLocaleRedirects()` for localized routes.** Routes are
    registered under a prefix from `LaravelLocalization::setLocale()`, which reads
    the request. In tests the route file loads during bootstrap, before a request
    exists, so routes register unprefixed while the redirect middleware still
    expects a prefix. This is the same limitation behind the route-caching note
    above.

# Other relevant URLs:

-   For local test emails see: http://mail.lndo.site/

# Translations

Translation files live in `resources/lang` (`de`, `en`, `fr`) and are edited
directly — Translation.io is no longer used. The `langman:*` artisan commands
help with maintenance:

-   `lando artisan langman:show <group>` — compare a group across all locales
-   `lando artisan langman:missing` — find keys that still need a value
-   `lando artisan langman:sync` — pick up new keys used in views

The commands are vendored in `app/Support/Langman` rather than pulled in as a
dependency; see that directory for why.

# Deployments

See [docs/release.md](docs/release.md) for release and hotfix instructions, including git config and deployment steps.

## User management

See [docs/user-management.md](docs/user-management.md) for superadmin rights, the admin area and impersonation.

# Documentation Index

-   [Docker & Local Development](docs/docker.md)
-   [Sentry CLI Integration](docs/sentry.md)
-   [Release & Hotfix Process](docs/release.md)
-   [User & Team Management](docs/user-management.md)

# Custom Application Configuration

## Invite only

-   `INVITE_ONLY` — `true` closes sign-up: only an address with a pending team invitation can get an account, on the sign-up page and through Office sign-up (the add-in is sent back with `?status=invite_only`). Only superadmins create teams, and nobody gets a personal team. Default `false`. See [docs/user-management.md](docs/user-management.md).

## NLP API Sync

Use for the integration with the NLP API.

-   `APP_NLP_API_SYNC_ENDPOINT` — Main endpoint for NLP API sync
-   `APP_NLP_API_SYNC_ENDPOINT_2` — (Optional) Secondary endpoint for NLP API sync
-   `APP_NLP_API_SYNC_USER` — Username for NLP API sync
-   `APP_NLP_API_SYNC_PASSWORD` — Password for NLP API sync
-   `APP_NLP_API_SYNC_CONFIGS` — Enable/disable sync configs
-   `APP_NLP_API_SYNC_DELAY_PER_COUNT` — Delay per sync count (default: 0.1)

## Witty GPT

Witty GPT reviews LLM responses through the NLP API. Not every backend supports
LLMs, so the feature can be switched off for a whole installation.

-   `APP_LLM_ENABLED` — Enable Witty GPT (default: `true`)

When set to `false` the `/prompt` route is not registered, the navigation entry
and the per-team toggle are hidden, and every team is reported to the NLP API as
having LLM alternatives disabled regardless of its own setting.

Teams still opt in individually under team privacy settings; this is the
installation-wide switch above that.

## Browser Version Tracking

Enabled the browser extension integration.

-   `BROWSER_VERSION_CHROME` — Latest supported Chrome version
-   `BROWSER_VERSION_EDGE` — Latest supported Edge version
-   `BROWSER_VERSION_FIREFOX` — Latest supported Firefox version

## PostHog Analytics

PostHog collects the statistics the Witty client applications (browser extension, Microsoft Word Add-in) and the dashboard's server send, which the dashboard's analytics pages show. The dashboard loads no tracking script in the browser.

-   `POSTHOG_ENABLED` — Enable PostHog analytics
-   `POSTHOG_API_KEY` — API key for PostHog
-   `POSTHOG_HOST` — Host URL for PostHog instance, the app host (for example `https://eu.posthog.com`), since the dashboard also calls PostHog's private API
-   `POSTHOG_DEBUG` — Enable debug mode for PostHog
-   `POSTHOG_PROJECT_ID` — Project ID for PostHog
-   `POSTHOG_PERSONAL_API_KEY` — Personal API key for PostHog. Besides reading insights, it asks PostHog to delete the person, events and session recordings of a deleted account; PostHog removes them in the background. That needs the `person:write` scope, so use a key limited to this project. Without the key or `POSTHOG_PROJECT_ID`, a deletion logs an error naming the person to delete by hand.
-   `POSTHOG_INSIGHTS_CACHE_TIME` — Cache time for insights (seconds)
-   `POSTHOG_DASHBOARD_USER_ID` — Dashboard user ID override, for local development only: every user then reports as this one PostHog person, and deleting an account leaves PostHog alone
-   `POSTHOG_DASHBOARD_TEAM_ID` — Dashboard team ID override, for local development only: every team then reports as this one group, and deleting a team leaves it alone
-   `POSTHOG_RATE_LIMIT` — API rate limit (default: 16)
-   `POSTHOG_RATE_INTERVAL_SECONDS` — API rate interval in seconds (default: 60)
-   `POSTHOG_RATE_MULTIPLIER` — API rate multiplier (default: 3)

## Microsoft Office SSO

Relevant for the Microsoft Word Add-in integration.

-   `MICROSOFT_CLIENT_ID` — Client ID of the add-in's Entra app registration
-   `MICROSOFT_CLIENT_REDIRECT_URI` — Where the add-in's sign-in dialog returns to
-   `MICROSOFT_TENANT_ID` — (Optional) The organisation's Entra tenant ID, a GUID; tokens from any other tenant are refused

The app registration has to issue the optional claims `email` and `xms_edov` in the **access** token, which is what the add-in receives (**Token configuration** → **Add optional claim** → token type **Access**). A user is identified by the Microsoft account linked to their dashboard account or, failing that, by `email`, and `email` counts only with `xms_edov: true`, which says the tenant verified the address's domain. `preferred_username` is not used: Microsoft documents it, like `email`, as a value a tenant's admin can set. A Microsoft account is linked to the dashboard account the first time it signs in or signs up through the add-in with a verified email; without the two claims, only users already linked get in.
