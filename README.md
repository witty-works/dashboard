# Introduction

Dashboard for Witty

Handles registration, SSO, user license management, corporate rules and personal/team statistics.

* https://jetstream.laravel.com/
* https://laravel.com/docs/8.x/socialite

# Production

Hosted at platform.sh

* https://admin.witty.works

For documentation see https://docs.platform.sh/

Email sending on platform.sh is enabled for master and develop branches only.

# Local Install

Fetch code from https://github.com/witty-works/dashboard

* Clone Admin UI
  * Make sure to add your SSH key to github
  * Run `git@github.com:witty-works/dashboard.git`
  * Run `cp .env.example .env`
  * Install Lando https://docs.lando.dev/basics/installation.html
    * On Windows 10 use WSL2 https://blog.calevans.com/2020/06/18/making-lando-work-inside-wsl2/
  * Run `lando start`
  * Run `lando composer install`
  * Run `lando artisan migrate`
  * Run `lando artisan storage:link`
  * Run `lando npm install --save`
  * Run `lando npm run dev`
  * Install the lando certificate in your browser
    * https://docs.lando.dev/config/security.html#certificates
  * Go to the website and create an account

* Install Platform.sh CLI https://docs.platform.sh/development/cli.html
  * Run `platform login`
  * Run `platform project:set-remote`
  * Run `platform list` to find out what commands are available
  * Run `platform help [command]` to find out details about a command

* Install Sentry CLI https://docs.sentry.io/product/cli/installation/
    * Run `sentry-cli login`
    * Run `cp .sentryclirc.example .sentryclirc` 
    * Edit `.sentryclirc` to add the auth token from https://sentry.io/settings/account/api/auth-tokens/

# Development

* Run via Lando (Docker)
  * Run `lando start`
  * Run `lando npm run watch`

* Goto `https://dashboard.lndo.site`

Use gitflow to do feature or hotfix branches:
https://nvie.com/posts/a-successful-git-branching-model/

See here for installation instructions:
https://github.com/nvie/gitflow/wiki/Installation

# Other relevant URLs:

* For local test emails see: http://mail.lndo.site/

# Using Stripe in development

* Install Stripe CLI https://stripe.com/docs/stripe-cli
* Run stripe_listen.sh

# Translations

Add missing translation keys from blade views and javascript code

```bash
lando artisan langman:sync --create
```

If you notice translation keys being added that are not proper strings,
then remove those translations keys and instead add this key to the
`langman.ignore_keys` configuration setting and add the keys manually
into the translation file instead.

Sync translations with translation.io

```bash
lando artisan translation:sync_and_purge
```

Export translations to javascript

```bash
lando artisan export:messages-flat
```

# Deployments

In `~/.gitconfig` add make sure you have the following setting

```
[tag]
        sort = -version:refname
```

## Make a release

* Make sure translations are up to date
* Check https://github.com/witty-works/admin-ui/compare/main...dev
* Check that staging works fine
* Go to the console
  * `./make-release.sh release` // to start a minor release, replace `release` with `major` to start a major release
  * `./make-release.sh finish` // use `:wq` to save, use `i` to insert the copied milestone URL and `esc` to get out of insert mode
  * `./make-release.sh finalize`

## Make a hotfix

* Go to the console
  * `./make-release.sh hotfix` // to start a hotfix release
  * Make your changes, ie. `git commit`, ensure that they work locally, to test remotely use `platform environment:push`
  * Make sure translations are up to date but *do not* run `lando artisan translation:sync_and_purge`
    * Instead manually add the translations for `de` and `fr` if new translations were added for `en`
  * Optionally test the hotfix branch with production data
    * Push the branch got gitlab `git push origin hotfix/[new version]`
    * To activate the branch on platform.sh run `platform environment:activate`
    * *ATTENTION* This will use production data, so be careful and use your production passwords etc
    * If testing email related features, enable email sending for the hotfix environment on platform.sh
  * `./make-release.sh finish` // use `:wq` to save, use `i` to insert the copied milestone URL and `esc` to get out of insert mode
  * `./make-release.sh finalize`

# Synchronize stacks between Rokka organizations

Production Rokka Org: wittyworks-admin-ui
Develop Rokka Org: wittyworks-admin-ui-dev

```
cp rokka.yml ~/.rokka.yml
./vendor/bin/rokka-cli stack:clone-all --source-organization=source destination --overwrite
```