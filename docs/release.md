# Release & Hotfix Process

How the dashboard's maintainers make a release, a release candidate or a fix to a release. Every change reaches `main`, the only long-lived branch, through a pull request on which CI passed; a release is a tag on `main`, made by a workflow started by hand. Nothing needs installing locally. The workflow is untried until its first run, which makes 2.0.0-rc1.

## What the release workflow does

The **Release** workflow (`.github/workflows/release.yaml`) releases `main`'s latest commit:

1.  It stops unless CI's `php`, `js` and `browser` jobs passed on that commit.
2.  It works out the version from the existing tags (`.github/scripts/release-version.sh`).
3.  It creates the tag and a GitHub release whose notes list the pull requests merged since the previous version. A release candidate is marked as a pre-release.
4.  It builds the image and publishes it under the version ([Production image](docker.md#production-image)).
5.  It creates the Sentry release with the commits since the previous version ([sentry.md](sentry.md#release-reported-to-sentry)).

The run summary names the version and the image digest to pin deployments by.

| Type | After `1.42.9` | After `2.0.0-rc1` |
| --- | --- | --- |
| `rc major` | `2.0.0-rc1`, the first candidate for 2.0.0 | `2.0.0-rc2` |
| `rc` | refused: no candidate to continue | `2.0.0-rc2` |
| `major` | `2.0.0` | `2.0.0`, the release itself |
| `minor` | `1.43.0` | `1.43.0` |
| `patch` | `1.42.10` | `1.42.10` |

`rc minor` and `rc patch` start candidates for the other release types. `major`, `minor` and `patch` count from the last release, never from a candidate.

## Make a release

-   Make sure translations are up to date: `lando artisan langman:missing` lists keys that still need a value, and new `en` keys need their `de` and `fr` translations.
-   Look through what the release brings: the pull requests merged into `main` since the last tag.
-   Check that staging works fine.
-   Wait until CI has finished on `main`'s latest commit: the workflow refuses a commit CI has not passed on yet.
-   In GitHub, open **Actions** → **Release** → **Run workflow**, leave "Use workflow from" on `main`, choose the type, and run it.

## Make a release candidate

A release candidate is tagged `x.y.z-rcN` and published as an image of that name, for a deployment to try before the release.

-   Run the **Release** workflow with `rc major` (or `rc minor`, `rc patch`) for the first candidate: after `1.42.9`, `rc major` makes `2.0.0-rc1`.
-   Run it with `rc` for each later candidate: `2.0.0-rc2`.
-   When a candidate is good, run it with the same type as the first candidate (`major` after `2.0.0-rc2`) to release `2.0.0` from `main`.

## Fix a release

-   Fix it on `main` through a pull request, as any change, then run the **Release** workflow with `patch`.
-   To fix an older release while `main` holds changes that are not ready, branch from that release's tag, fix it there, and have an admin push the tag (`1.42.10`) by hand: the tag push builds the image (`.github/workflows/release-image.yaml`). Create the Sentry release by hand with `sentry-cli` (see [sentry.md](sentry.md)), and bring the fix into `main` with a pull request.

## Settings the workflow relies on

-   The repository secret `SENTRY_AUTH_TOKEN`, a Sentry token allowed to create releases in the `witty-works-ag` organisation. Without it, the Sentry step is skipped and says so in the run summary.
-   Version tags may be created only by admins and by the release workflow (#1019), so nobody else can publish an image.
