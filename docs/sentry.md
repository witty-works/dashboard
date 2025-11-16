# Sentry CLI Integration

This document describes how to use Sentry CLI for error tracking and release management.

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
