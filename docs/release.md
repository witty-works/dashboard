# Release & Hotfix Process

## Make a release

-   Make sure translations are up to date
-   Check https://github.com/witty-works/dashboard/compare/main...dev
-   Check that staging works fine
-   Go to the console
    -   `make-release.sh release` // to start a minor release, replace `release` with `major` to start a major release
    -   `make-release.sh finish` // use `:wq` to save, use `i` to insert the copied milestone URL and `esc` to get out of insert mode
    -   `make-release.sh finalize`

## Make a hotfix

-   Go to the console
    -   `make-release.sh hotfix` // to start a hotfix release
    -   Make your changes, ie. `git commit`, ensure that they work locally, to test remotely use `platform environment:push`
    -   Make sure translations are up to date but _do not_ run `lando artisan translation:sync_and_purge`
        -   Instead manually add the translations for `de` and `fr` if new translations were added for `en`
    -   Optionally test the hotfix branch with production data
        -   Push the branch got gitlab `git push origin hotfix/[new version]`
        -   To activate the branch on platform.sh run `platform environment:activate`
        -   _ATTENTION_ This will use production data, so be careful and use your production passwords etc
        -   If testing email related features, enable email sending for the hotfix environment on platform.sh
    -   `make-release.sh finish` // use `:wq` to save, use `i` to insert the copied milestone URL and `esc` to get out of insert mode
    -   `make-release.sh finalize`

## Git config for releases

In `~/.gitconfig` add the following setting:

```
[tag]
        sort = -version:refname
```
