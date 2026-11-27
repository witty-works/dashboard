# Sentry CLI Integration

This document describes how to use Sentry CLI for error tracking and release management, which release the dashboard reports, and what reaches Sentry.

## Release reported to Sentry

Errors are filed under the version the running image was built from: the release workflow passes the version tag (`2.0.0`, `2.0.0-rc1`), exactly as pushed, into the image as `APP_VERSION`, and `config/sentry.php` reports it, in the browser too. That is the name make-release gives the Sentry release it creates with `sentry-cli`, so events and commits meet in one release. The version is no longer written into a file, so the first line of `make-release`, its list of version files, is empty. An image built without a version tag reports its commit (`GIT_REVISION`), and one built without either reports no release. `SENTRY_RELEASE` overrides both.

## What reaches Sentry

-   **Error events** with the exception, its stack trace and the request's address, without request bodies (`max_request_body_size` is `never`) and without IP addresses or cookies (`send_default_pii` is off).
-   **No query values and no log lines as breadcrumbs:** sessions and the queue are in the database, so query values would carry addresses, password hashes and job payloads, and log lines carry names and addresses. Breadcrumbs still list the queries themselves, without their values.
-   **Exception messages as written.** Messages that name a person end up in Sentry too, so keep addresses and response bodies out of them.

## Installation

-   Download and install Sentry CLI: https://docs.sentry.io/product/cli/installation/

## Setup

-   Login to Sentry CLI:
    ```bash
    sentry-cli login
    ```
-   Copy the example config:
    ```bash
    cp .sentryclirc.example .sentryclirc
    ```
-   Edit `.sentryclirc` to add your auth token from https://sentry.io/settings/account/api/auth-tokens/

## Notes

-   For more details, see the official Sentry CLI documentation.
