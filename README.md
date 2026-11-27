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

**Note:** `php artisan route:cache` works with the localization package in use
(`niels-numbers/laravel-localizer`), which registers every localized route as a
plain route. The production image caches routes when it starts.

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

## Browser tests

PHPUnit sees markup; what only a rendered page shows is checked in Chromium with Playwright (`tests/browser`, `playwright.config.js`):

-   **No WCAG 2.2 A or AA violation axe can detect** on the guest pages and the main signed-in pages. Embedded players (the academy's YouTube videos) are left out, as their markup is the provider's.
-   **Keyboard and focus:** the skip link is the first stop, visible above the sidebar, and leads to the main content; "Edit" in a list moves focus to the field it fills.
-   **Reflow:** the pages do not scroll sideways at 320px.
-   **The analytics page,** with canned data instead of PostHog's: axe once the charts are drawn, the legend's checkboxes by keyboard, the charts' data tables, and the refresh button that keeps focus.
-   **No console errors or uncaught exceptions,** which includes anything the Content-Security-Policy blocks.

CI runs them in their own job. To run them locally, against the `testing` database (a PHPUnit run empties it again):

```
npm run build
php artisan storage:link
POSTHOG_ENABLED=false APP_NLP_API_SYNC_ENDPOINT= php artisan migrate:fresh --seed --seeder=BrowserTestSeeder --force
npx playwright install chromium
npm run test:browser
```

The tests start PHP's built-in server on port 8000 themselves, with the app's address on plain HTTP. axe finds only part of what WCAG asks; a check with a screen reader (VoiceOver or NVDA) before a release covers the rest.

Two things to know when writing tests:

-   **Assert the view as well as the status.** Use `assertViewIs(...)` alongside
    `assertOk()` so a test cannot pass against the fallback error page.
-   **Call `$this->withoutLocaleRedirects()` for localized routes.** Routes are
    registered under a prefix from `LaravelLocalization::setLocale()`, which reads
    the request. In tests the route file loads during bootstrap, before a request
    exists, so routes register unprefixed while the redirect middleware still
    expects a prefix.

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

## Production image

Deployments run the Docker image that a version tag publishes to `ghcr.io/witty-works/dashboard`, pinned by digest. One image serves as web server, queue worker and migration step, and takes all its configuration from the environment. See [docs/docker.md](docs/docker.md#production-image) for the roles, the settings a deployment needs and the first start.

## Setting up a deployment

A deployment runs the dashboard image (web server and queue worker, plus a migration step per release) behind a reverse proxy that terminates TLS, with a MariaDB database, an SMTP service and the [NLP API](https://github.com/witty-works/nlp_api) with its Redis. The dashboard sends every user's and team's settings to the NLP API and manages the API keys people create there; the write page calls the API from the browser.

[docs/deployment.md](docs/deployment.md) walks through the setup in order and ends with the [technical checklist](docs/deployment.md#technical-checklist-before-going-live) to work through before going live.

## Features a deployment switches on or off

| Feature | What people get | What it involves | Setting |
| --- | --- | --- | --- |
| Invitation only | Nobody gets an account without an invitation; superadmins create the teams | Deciding who invites whom | `INVITE_ONLY`, off by default |
| Write page | Writing and checking texts in the browser, signed in to the dashboard | Nothing beyond the setup | `PASSPORT_EDITOR_CLIENT_ID` and `NLP_API_PUBLIC_URL`; the page says it is not set up until both are set |
| API keys | Keys for tools such as the Witty editor in other applications | Nothing beyond the setup | On with the NLP API connected |
| Witty GPT and AI rewrites | Texts reviewed and rewritten with a language model | A language model provider, which receives the texts; each team opts in, and superadmins limit its requests per minute | `APP_LLM_ENABLED`, on by default, and a model on the NLP API |
| Browser extension | Witty in text fields on other websites | Installing the extension; it signs in to this deployment | `BROWSER_EXTENSION_ENABLED`, on by default |
| Word add-in | Witty in Microsoft Word | A Microsoft 365 administrator registering the add-in | `MICROSOFT_*`, off without them |
| Usage statistics | Analytics pages on how Witty is used, per person and per team | A PostHog instance, which receives events about what people do with Witty | `POSTHOG_ENABLED`, off by default; the pages themselves with `ANALYTICS_PAGES_ENABLED` |
| Error reports | Errors reach the operators | A Sentry project, which receives error details | `SENTRY_LARAVEL_DSN`, off without it |

Each setting is described under [Custom Application Configuration](#custom-application-configuration).

## Deployment checklist

For anyone proposing the dashboard to their organisation: the decisions to take, the people to involve and what to prepare before it goes live. Each group names who usually takes care of it. The technical side, for whoever runs the servers, is the [technical checklist](docs/deployment.md#technical-checklist-before-going-live).

**What your organisation commits to**, with management and IT

-   **Someone runs it,** usually IT or a hosting partner. The dashboard and the NLP API are services your organisation hosts or has hosted: servers with a database, an update for each release, backups, and someone who notices when it stops working ([Services a deployment runs](docs/deployment.md#services-a-deployment-runs)).
-   **A support address with someone answering it.** People are told to write there when something goes wrong or they need access.
-   **A trial with a release candidate first.** A small group uses a release candidate before everyone gets the release ([Releases and release candidates](docs/deployment.md#releases-and-release-candidates)).

**Privacy and legal**, with your data protection officer and legal team

-   **Know what happens to the texts people write.** Every text people check goes to the NLP API. People use Witty when they write about disability, gender, origin, religion or health, so treat these texts as sensitive personal data. The NLP API is built not to store or log them (its [ethics notes](https://github.com/witty-works/nlp_api/blob/dev/docs/ethics.md#treat-all-submitted-text-as-sensitive-personal-data)). Spelling checks and AI features send texts on to further services, but only where they are set up.
-   **Write a privacy policy and terms of use of your own.** The dashboard shows your documents and asks people to agree to them when they sign up; it comes with none. The privacy policy names every service that receives personal data: the hosting, the NLP API, the mail service, and, where used, the language model provider, the spelling service, usage statistics (PostHog) and error reports (Sentry).
-   **Whether to collect usage statistics.** They are off unless switched on. When on, PostHog receives events about what people do with Witty, and for teams with "Store context" on also a few characters around each flagged word. Deleting an account deletes the person's data there too, once PostHog is set up for it ([technical checklist](docs/deployment.md#technical-checklist-before-going-live)).
-   **Whether to offer AI features,** and with which provider. Witty GPT and AI rewrites send texts to a language model provider; each team then decides whether to use them.

**Who uses it, and how**, with whoever introduces Witty in the organisation

-   **Decide between open sign-up and invitation only.** With invitations only, only people with an invitation get an account, and superadmins create the teams.
-   **A few superadmins** who create the teams and manage accounts. They have to use two-factor sign-in.
-   **An owner for each team.** Team owners and admins invite their members and choose the team's language guidelines: the gender-inclusive spelling, which kinds of language Witty points out, and whether members may change them.
-   **The tools people use:** the write page in the browser, the browser extension, the Word add-in. The Word add-in needs a Microsoft 365 administrator to register it. See [Features a deployment switches on or off](#features-a-deployment-switches-on-or-off).

**Accessibility**, with someone who uses a screen reader or tests with one

-   **Someone checks each release with a screen reader,** such as VoiceOver or NVDA: the automatic [browser tests](#browser-tests) find only part of what accessibility guidelines ask.

## User management

See [docs/user-management.md](docs/user-management.md) for superadmin rights, the admin area and impersonation.

# Documentation Index

-   [Deploying the dashboard](docs/deployment.md)
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
-   `APP_NLP_API_SYNC_ENDPOINT_2` — (Optional) Secondary endpoint for NLP API sync. API keys are created on the main endpoint only, so both must share one Redis (see [docs/user-management.md](docs/user-management.md#api-keys))
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

## Browser extension

-   `BROWSER_EXTENSION_ENABLED` — The browser extension's sign-in and API routes, for people who use the extension (default `true`). The dashboard does not promote the extension; its write page does what the extension did.

## PostHog Analytics

PostHog collects the statistics the Witty client applications (browser extension, Microsoft Word Add-in) and the dashboard's server send, which the dashboard's analytics pages show. The dashboard loads no tracking script in the browser.

-   `POSTHOG_ENABLED` — Enable PostHog analytics
-   `ANALYTICS_PAGES_ENABLED` — Show the analytics pages, their API and menu entries, and the team setting for who may see them (default `true`). They show only while `POSTHOG_ENABLED` is on as well. Turn it off to collect events before the pages have anything worth showing; the personal consent to being counted stays while events are collected
-   `POSTHOG_API_KEY` — API key for PostHog
-   `POSTHOG_HOST` — Host URL for PostHog instance, the app host (for example `https://eu.posthog.com`), since the dashboard also calls PostHog's private API
-   `POSTHOG_DEBUG` — Enable debug mode for PostHog
-   `POSTHOG_PROJECT_ID` — Project ID for PostHog
-   `POSTHOG_PERSONAL_API_KEY` — Personal API key for PostHog. Besides reading insights, it asks PostHog to delete the person, events and session recordings of a deleted account; PostHog removes them in the background. That needs the `person:write` scope, so use a key limited to this project. Without the key or `POSTHOG_PROJECT_ID`, a deletion logs an error naming the person to delete by hand.
-   `POSTHOG_INSIGHTS_CACHE_TIME` — Cache time for insights (seconds)
-   `POSTHOG_DASHBOARD_USER_ID` — Dashboard user ID override, for local development only: every user then reports as this one PostHog person, and deleting an account leaves PostHog alone
-   `POSTHOG_DASHBOARD_TEAM_ID` — Dashboard team ID override, for local development only: every team then reports as this one group, and deleting a team leaves it alone. It is also the team id the NLP API sees, so every team's settings go to one organisation there, its API keys are listed together, and deleting any team revokes them all
-   `POSTHOG_RATE_LIMIT` — API rate limit (default: 16)
-   `POSTHOG_RATE_INTERVAL_SECONDS` — API rate interval in seconds (default: 60)
-   `POSTHOG_RATE_MULTIPLIER` — API rate multiplier (default: 3)

## Microsoft Office SSO

Relevant for the Microsoft Word Add-in integration.

-   `MICROSOFT_CLIENT_ID` — Client ID of the add-in's Entra app registration
-   `MICROSOFT_CLIENT_REDIRECT_URI` — Where the add-in's sign-in dialog returns to
-   `MICROSOFT_TENANT_ID` — (Optional) The organisation's Entra tenant ID, a GUID; tokens from any other tenant are refused

The app registration has to issue the optional claims `email` and `xms_edov` in the **access** token, which is what the add-in receives (**Token configuration** → **Add optional claim** → token type **Access**). A user is identified by the Microsoft account linked to their dashboard account or, failing that, by `email`, and `email` counts only with `xms_edov: true`, which says the tenant verified the address's domain. `preferred_username` is not used: Microsoft documents it, like `email`, as a value a tenant's admin can set. A Microsoft account is linked to the dashboard account the first time it signs in or signs up through the add-in with a verified email; without the two claims, only users already linked get in.

## Security headers

Every response carries a Content-Security-Policy, `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin` and a Permissions-Policy that turns off the camera, microphone, location, payment and other device features. `App\Http\Middleware\SecurityHeaders` sets them and explains each exception in the policy. No page may be framed by another site. The dashboard does not send `Strict-Transport-Security`; the reverse proxy in front of it does.

The policy lets pages load scripts, styles, fonts and images only from the dashboard itself. The exceptions are images from `https://www.witty.works`, the YouTube videos on the academy page, the NLP API address in `NLP_API_PUBLIC_URL` and the Sentry host in `SENTRY_LARAVEL_DSN`. An inline `<script>` in a view needs `@cspNonce` (`<script @cspNonce>`), and inline event handlers such as `onclick=` do not run: attach listeners from a script instead.

-   `CSP_REPORT_ONLY` — `true` sends the policy as `Content-Security-Policy-Report-Only`: browsers report what it would block, but block nothing. Use it for a first rollout. Default `false`
-   `CSP_REPORT_URI` — (Optional) Where browsers send violation reports, for example the Sentry project's security endpoint (**Project Settings** → **Security Headers** in Sentry). It must be a plain `https` URL; anything else is left out. Without it, violations show only in the browser's console. Reports include the page's query string, so check Sentry's data scrubbing before sending them there
-   `CSP_ENABLED` — `false` sends no Content-Security-Policy at all, for local debugging only, for example of Laravel's exception page. Default `true`
