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
    -   Make your changes, ie. `git commit`, ensure that they work locally
    -   Make sure translations are up to date
        -   Manually add the translations for `de` and `fr` if new translations were added for `en`
        -   `lando artisan langman:missing` lists keys that still need a value
    -   `make-release.sh finish` // use `:wq` to save, use `i` to insert the copied milestone URL and `esc` to get out of insert mode
    -   `make-release.sh finalize`

## Git config for releases

In `~/.gitconfig` add the following setting:

```
[tag]
        sort = -version:refname
```
