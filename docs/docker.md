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

## Troubleshooting

-   If you encounter issues with Docker containers, check Lando logs and restart containers as needed.
-   For advanced configuration, see the [Lando documentation](https://docs.lando.dev/config/).

## Optional Features

-   Local test emails: http://mail.lndo.site/
-   Sentry CLI integration: See [docs/sentry.md](sentry.md)

## Notes

-   Some features (e.g., route caching) are not supported due to multilingual route extension. See main README for details.
