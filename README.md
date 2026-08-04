# Introduction

Dashboard for Witty

Handles registration, SSO, user license management, organization rules and
personal/team statistics by integrating with https://github.com/witty-works/nlp_api as well as https://github.com/witty-works/browser-extension and https://github.com/witty-works/word-plugin

Based on

-   https://jetstream.laravel.com/
-   https://laravel.com/docs/8.x/socialite

For deployment we recommend https://docs.platform.sh/

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
    -   Run `lando artisan lumki:setup` (answer yes, ie. hit enter, for every question)

-   Install Platform.sh CLI https://docs.platform.sh/development/cli.html

    -   Run `platform login`
    -   Run `platform project:set-remote` (select `dashboard`)
    -   Run `platform list` to find out what commands are available
    -   Run `platform help [command]` to find out details about a command

-   Install Sentry CLI https://docs.sentry.io/product/cli/installation/
    -   Run `sentry-cli login`
    -   Run `cp .sentryclirc.example .sentryclirc`
    -   Edit `.sentryclirc` to add the auth token from
        https://sentry.io/settings/account/api/auth-tokens/

**Note:** Route caching is _not supported_ by the multilingual route extension used in this project. Avoid running `php artisan route:cache` or similar commands, as it may break route localization.

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

Two things to know when writing tests:

-   **Assert on the view, not the status code.** The fallback route renders
    `errors.404` with a **200**, so `assertOk()` alone passes against the error
    page. Use `assertViewIs(...)`.
-   **Call `$this->withoutLocaleRedirects()` for localized routes.** Routes are
    registered under a prefix from `LaravelLocalization::setLocale()`, which reads
    the request. In tests the route file loads during bootstrap, before a request
    exists, so routes register unprefixed while the redirect middleware still
    expects a prefix. This is the same limitation behind the route-caching note
    above.

# Other relevant URLs:

-   For local test emails see: http://mail.lndo.site/

# Translations

See [docs/docker.md](docs/docker.md) for translation workflow and troubleshooting.

# Deployments

See [docs/release.md](docs/release.md) for release and hotfix instructions, including git config and deployment steps.

## User management

See [docs/subscriptions.md](docs/subscriptions.md) for user management, superadmin rights, and impersonation instructions.

# Documentation Index

-   [Docker & Local Development](docs/docker.md)
-   [Stripe CLI Integration](docs/stripe.md)
-   [Sentry CLI Integration](docs/sentry.md)
-   [Release & Hotfix Process](docs/release.md)
-   [Subscription & Team Management](docs/subscriptions.md)

# Custom Application Configuration

## NLP API Sync

Use for the integration with the NLP API.

-   `APP_NLP_API_SYNC_ENDPOINT` — Main endpoint for NLP API sync
-   `APP_NLP_API_SYNC_ENDPOINT_2` — (Optional) Secondary endpoint for NLP API sync
-   `APP_NLP_API_SYNC_USER` — Username for NLP API sync
-   `APP_NLP_API_SYNC_PASSWORD` — Password for NLP API sync
-   `APP_NLP_API_SYNC_CONFIGS` — Enable/disable sync configs
-   `APP_NLP_API_SYNC_DELAY_PER_COUNT` — Delay per sync count (default: 0.1)

## Browser Version Tracking

Enabled the browser extension integration.

-   `BROWSER_VERSION_CHROME` — Latest supported Chrome version
-   `BROWSER_VERSION_EDGE` — Latest supported Edge version
-   `BROWSER_VERSION_FIREFOX` — Latest supported Firefox version

## Helphero Integration

Used for application overlays for onboarding.

-   `HELPHERO_JS_ENABLED` — Enable Helphero JS widget
-   `HELPHERO_APP_ID` — Helphero App ID

## Translation.io

Only used for local development to ease translation maintenance.

-   `TRANSLATIONIO_KEY` — API key for Translation.io integration

## PostHog Analytics

Posthog is used both for analytics of usage of the dashboard as well as for collecting statistics in the Witty client applications (browser extension, Microsoft Word Add-in).

-   `POSTHOG_ENABLED` — Enable PostHog analytics
-   `POSTHOG_JS_ENABLED` — Enable PostHog JS tracking
-   `POSTHOG_API_KEY` — API key for PostHog
-   `POSTHOG_HOST` — Host URL for PostHog instance
-   `POSTHOG_DEBUG` — Enable debug mode for PostHog
-   `POSTHOG_PROJECT_ID` — Project ID for PostHog
-   `POSTHOG_PERSONAL_API_KEY` — Personal API key for PostHog
-   `POSTHOG_INSIGHTS_CACHE_TIME` — Cache time for insights (seconds)
-   `POSTHOG_DASHBOARD_USER_ID` — Dashboard user ID override
-   `POSTHOG_DASHBOARD_TEAM_ID` — Dashboard team ID override
-   `POSTHOG_RATE_LIMIT` — API rate limit (default: 16)
-   `POSTHOG_RATE_INTERVAL_SECONDS` — API rate interval in seconds (default: 60)
-   `POSTHOG_RATE_MULTIPLIER` — API rate multiplier (default: 3)

## Microsoft Office SSO

Relevant for the Microsoft Word Add-in integration.

-   `OFFICE365_CLIENT_ID` — Client ID for Microsoft Office 365 SSO
-   `OFFICE365_CLIENT_SECRET` — Client Secret for Microsoft Office 365 SSO
-   `OFFICE365_REDIRECT_URI` — Redirect URI for Office 365 SSO callback
-   `OFFICE365_TENANT_ID` — (Optional) Tenant ID for restricting SSO to a specific organization
