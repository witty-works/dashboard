# Release & Hotfix Process

The steps below use `make-release.sh` from [witty-works/make-release](https://github.com/witty-works/make-release), which builds on git flow; its README says how to install it. The `make-release` file in this repository's root is its configuration for the dashboard: no version file (the image carries its version tag as `APP_VERSION`, see [sentry.md](sentry.md#release-reported-to-sentry)), and the Sentry organisation and project `witty-works-ag` and `dashboard`.

Every version tag publishes an image: `x.y.z` for a release, `x.y.z-rcN` for a release candidate ([Production image](docker.md#production-image)).

## Make a release

-   Make sure translations are up to date
-   Check https://github.com/witty-works/dashboard/compare/main...dev
-   Check that staging works fine
-   Go to the console
    -   `make-release.sh minor` // to start a minor release; `major` or `patch` for the others
    -   `make-release.sh finish` // pushes the branch and opens a pull request into `main`
    -   Merge the pull request once the checks pass, with a merge commit (not squash or rebase)
    -   `make-release.sh finalize` // tags the merge and merges it into `dev`; in the editor for the tag message use `i` to insert the copied milestone URL, `esc` to get out of insert mode and `:wq` to save

## Make a release candidate

A release candidate is tagged `x.y.z-rcN` and published as an image of that name, for a deployment to try before the release.

-   Go to the console
    -   `make-release.sh rc major` // the first candidate names the release it leads to: after `1.42.9`, `2.0.0-rc1`; `rc minor` and `rc patch` for the others
    -   `make-release.sh rc` // each later candidate counts on: `2.0.0-rc2`
    -   `make-release.sh finish`, merge the pull request, then `make-release.sh finalize`, as for a release
-   When the candidate is good, make the release itself with the same type: `make-release.sh major` after `2.0.0-rc2` starts `2.0.0`.

`make-release.sh next rc major` prints the version a command would start, without changing anything. This needs make-release with release-candidate support (witty-works/make-release#3); an older copy makes `1.42.9.1`, which publishes no image.

`finish` opens a pull request into `main` instead of merging, so `main` can take changes only through a pull request with passing checks without anyone having to approve it (#1019). This needs make-release that opens a pull request for each release (witty-works/make-release#4); an older copy pushes `main` directly, which such a `main` refuses.

## Make a hotfix

-   Go to the console
    -   `make-release.sh hotfix` // to start a hotfix release
    -   Make your changes, ie. `git commit`, ensure that they work locally
    -   Make sure translations are up to date
        -   Manually add the translations for `de` and `fr` if new translations were added for `en`
        -   `lando artisan langman:missing` lists keys that still need a value
    -   `make-release.sh finish` // pushes the branch and opens a pull request into `main`
    -   Merge the pull request once the checks pass, with a merge commit (not squash or rebase)
    -   `make-release.sh finalize` // tags the merge and merges it into `dev`; in the editor for the tag message use `i` to insert the copied milestone URL, `esc` to get out of insert mode and `:wq` to save
