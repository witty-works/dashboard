# Deploying the dashboard

This guide sets up a production deployment of the dashboard together with the
NLP API it depends on, in the order the work happens: services, secrets, the NLP
API's side, the dashboard's settings, the containers, the first start, a check
that it works, and how releases and backups go from then on. It is for whoever
operates a deployment. The README's [deployment checklist](../README.md#deployment-checklist)
lists what to confirm before going live; [docker.md](docker.md#production-image)
is the reference for the image itself, and the NLP API's own documentation for
the API.

## Contents

-   [Services a deployment runs](#services-a-deployment-runs)
-   [Secrets to generate](#secrets-to-generate)
-   [NLP API settings the dashboard relies on](#nlp-api-settings-the-dashboard-relies-on)
-   [Dashboard settings](#dashboard-settings)
-   [Containers and the reverse proxy](#containers-and-the-reverse-proxy)
-   [First start](#first-start)
-   [Checking that it works](#checking-that-it-works)
-   [Releases and release candidates](#releases-and-release-candidates)
-   [Backups and restoring](#backups-and-restoring)
-   [Regular tasks](#regular-tasks)

## Services a deployment runs

-   **The dashboard image** (`ghcr.io/witty-works/dashboard`) as three roles: `web` (plain HTTP on port 8080), `worker` (the queue) and `migrate` (once per release). See [Roles](docker.md#roles).
-   **MariaDB** for the dashboard: accounts, teams, settings, sessions, the queue.
-   **The [NLP API](https://github.com/witty-works/nlp_api)** with its **Redis**. The dashboard writes each user's and team's settings into the API, and the API keeps the API keys people create on the dashboard.
-   **A reverse proxy** in front of both, terminating TLS. The dashboard and the API each get a public `https` address of their own.
-   **An SMTP service** for invitations, password resets and address verification.
-   **Optional:** a PostHog instance for usage statistics, a Sentry project for errors, a language model provider for Witty GPT and AI rewrites (configured on the NLP API).

The web role also fetches the Google Fonts it serves from Google when it starts ([State](docker.md#state)), so it needs outbound access to Google. Visitors' browsers load the fonts from the dashboard, not from Google.

## Secrets to generate

Generate each once per deployment and keep it in the deployment's secret store, never in a repository:

| Secret | How | Where it goes |
| --- | --- | --- |
| Dashboard app key | `php artisan key:generate --show` | `APP_KEY` |
| OAuth signing keys | `php artisan passport:keys`, then the two PEM files | `PASSPORT_PRIVATE_KEY`, `PASSPORT_PUBLIC_KEY`, newlines as `\n` |
| Database password | your generator | `DB_PASSWORD`, and the database user |
| NLP API management credentials | a user name and a long random password | the API's `API_DOCS_USERNAME` and `API_DOCS_PASSWORD`, and the dashboard's `APP_NLP_API_SYNC_USER` and `APP_NLP_API_SYNC_PASSWORD` |
| API key hashing secret | a long random string | the API's `API_KEY_HMAC_KEY` |
| SMTP credentials | from the mail service | `MAIL_USERNAME`, `MAIL_PASSWORD` |

Some of these cannot be changed lightly:

-   **New OAuth signing keys sign everyone out:** every issued token stops working.
-   **A new `API_KEY_HMAC_KEY` invalidates every API key** people have created.
-   **A new `APP_KEY` invalidates two-factor secrets and recovery codes**, which are encrypted with it, so everyone with two-factor sign-in has to set it up again.

## NLP API settings the dashboard relies on

Deploy the NLP API first, following its documentation (`docs/setup.md` and `docs/configuration.md` there). These of its settings concern the dashboard:

-   **`DASHBOARD_URL`:** the dashboard's public address. The API fetches the dashboard's token signing keys from `{DASHBOARD_URL}/.well-known/jwks.json`.
-   **`DASHBOARD_ISSUER`:** the dashboard's `APP_URL`, exactly as the dashboard's tokens carry it ([Issuer](oauth.md#issuer)).
-   **`DASHBOARD_CLIENT_ID`:** the write page's OAuth client, created at the [first start](#first-start), and the browser extension's client if it signs in to this deployment, comma-separated.
-   **`MANAGEMENT_AUTH_ENABLED`** stays on (the default), with `API_DOCS_USERNAME` and `API_DOCS_PASSWORD` set: the dashboard signs in with them to write settings and manage API keys.
-   **`API_KEY_HMAC_KEY`** set, or the dashboard's API keys page cannot create keys.
-   **Redis on persistent storage**, as it holds the API keys. A second API endpoint (`APP_NLP_API_SYNC_ENDPOINT_2`) must use the same Redis.
-   **`REQUIRE_AUTH`** stays on (the default), so only signed-in users and API keys get texts checked.
-   **The language model**, only if Witty GPT and AI rewrites are wanted: the provider settings and an `LLM_ACCESS` policy. Texts then go to that provider, which the privacy policy has to name.
-   **`TEXTAREA_ENABLED`** stays off unless the API's own check page is wanted: the dashboard's write page does the same for signed-in users.

The API answers browser requests from any origin, without credentials, so the dashboard's write page needs no CORS setting there.

## Dashboard settings

All settings are environment variables; the image holds none. The required ones, with their production values, are in [Configuration a deployment sets](docker.md#configuration-a-deployment-sets):

-   the app key, `APP_URL` (the public `https` address), `APP_ENV=production` and `APP_DEBUG=false`;
-   the OAuth signing keys;
-   the database, with `QUEUE_CONNECTION=database` and `SESSION_DRIVER=database`;
-   mail (`MAIL_*`) and `SUPPORT_EMAIL`, the one address people are told to write to;
-   `PRIVACY_POLICY_FILE` and `TERMS_OF_SERVICE_FILE`, the deployment's own documents as Markdown files mounted into the container.

To connect the NLP API:

-   **`APP_NLP_API_SYNC_ENDPOINT`:** the API's address as the dashboard's containers reach it, with `APP_NLP_API_SYNC_USER` and `APP_NLP_API_SYNC_PASSWORD` (see [NLP API Sync](../README.md#nlp-api-sync)).
-   **`NLP_API_PUBLIC_URL`:** the API's address as browsers reach it, for the write page.

Which features a deployment offers is set with the switches in the README's [features a deployment switches on or off](../README.md#features-a-deployment-switches-on-or-off).

## Containers and the reverse proxy

-   **Pin the image by digest.** Each release's digest is in the run summary of the "release image" workflow ([Production image](docker.md#production-image)).
-   **Run `migrate` before the new `web` and `worker` start**, on every release. It exits when done.
-   **Put the `web` container behind the proxy only.** It trusts any proxy's `X-Forwarded-*` headers and redirects everything not marked `X-Forwarded-Proto: https`, so it must not be reachable directly ([Web server](docker.md#web-server)).
-   **Let the proxy send `Strict-Transport-Security`;** the dashboard sends the other security headers itself ([Security headers](../README.md#security-headers)).
-   **Health check:** `GET /up` on the web role answers `200` when the application boots and the database answers. Turn the image's health check off for `worker` and `migrate`.
-   **Restart the worker** when it exits: it stops after `QUEUE_MAX_TIME` (an hour by default) by design.
-   **Do not mount a volume over `storage/app`**, which holds data that is part of the code ([State](docker.md#state)).

## First start

1.  **Migrate:** run the `migrate` role against the empty database.
2.  **Create the first superadmin:** `php artisan superadmin:create you@example.org "Your Name"` with the image. It mails a link to set the password. A superadmin has to set up two-factor sign-in before using the admin pages ([Superadmins must use two-factor sign-in](user-management.md#superadmins-must-use-two-factor-sign-in)).
3.  **Create the write page's OAuth client:** `php artisan passport:editor-client`. Put the id it prints into `PASSPORT_EDITOR_CLIENT_ID` and `PASSPORT_FIRST_PARTY_CLIENTS` on the dashboard and into `DASHBOARD_CLIENT_ID` on the NLP API, then restart both ([The write page's flow](oauth.md#the-write-pages-flow)).
4.  **Only if the browser extension signs in to this deployment:** set `EXTENSION_OAUTH_REDIRECT_URIS`, run `php artisan passport:extension-client`, and add its id the same way ([Setup](oauth.md#setup)).
5.  **Only if the Word add-in is used:** register the add-in in Entra and set the `MICROSOFT_*` settings ([Microsoft Office SSO](../README.md#microsoft-office-sso)), and the NLP API's `OFFICE_SSO_CLIENT_ID`.
6.  **Create the first teams and invite their owners** from the admin area ([Admin area](user-management.md#admin-area)). With `INVITE_ONLY=true` nobody can sign up without an invitation.

If a superadmin loses their second factor, `php artisan two-factor:reset you@example.org` turns two-factor sign-in off for that account ([Recovering a locked-out account](user-management.md#recovering-a-locked-out-account)).

## Checking that it works

-   `GET /up` answers `200`.
-   The login page loads over `https`, without errors in the browser console. With `CSP_REPORT_ONLY=true` the console shows what the security headers would block.
-   An invitation mail arrives, and the link in it leads to sign-up.
-   The write page checks a text: that proves the OAuth client, `NLP_API_PUBLIC_URL` and the API's `DASHBOARD_*` settings.
-   A key created on the API keys page checks a text with the NLP API's `x-key` header, and revoking it stops it.
-   A changed language setting shows up in the API: `GET /user/configs?email=…` on the API, with the management credentials.

## Releases and release candidates

A version tag publishes the image: `1.43.0` for a release, `1.43.0-rc1` for a release candidate, each also tagged with its minor line (`1.43`) and never `latest` ([Production image](docker.md#production-image)). The release steps themselves are in [release.md](release.md).

To roll out a release or a release candidate:

1.  Read the release notes for new settings and migrations.
2.  Take a database backup.
3.  Run `migrate` with the new image, then start `web` and `worker` with it.
4.  Run the [checks above](#checking-that-it-works).

The dashboard and the NLP API are released separately. Settings the dashboard sends that an older API does not know are ignored there, so the dashboard can go first; a feature that needs the API takes effect once the API is updated.

## Backups and restoring

-   **MariaDB** holds everything of the dashboard's that cannot be rebuilt. Back it up daily and before every release.
-   **The NLP API's Redis** holds the API keys people created, and copies of every user's and team's settings. Back it up as well.
-   The containers keep nothing else: logs go to stderr, sessions and the queue are in the database, the cache rebuilds itself.

After restoring the NLP API's Redis, or after moving to a new one, run `php artisan nlp_api:sync` with the dashboard image: it sends every user's and team's settings to the API again. API keys cannot be sent again; they come back only from a Redis backup.

## Regular tasks

The image has no scheduler. Run these from the host's scheduler with the image, as `php artisan <command>`, as far as the deployment needs them ([Roles](docker.md#roles)):

-   `nlp_api:sync`: sends every user and team setting to the NLP API again, for instance nightly as a safety net;
-   `kpis`: stores each team's member and licence counts, which the analytics pages chart;
-   `statistics`: mails sign-up and team figures to `SUPPORT_EMAIL`;
-   `posthog:sync` and `writing-streak-kpi`: only with PostHog switched on.
