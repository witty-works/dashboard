#!/bin/bash
# The version a release would get, from the version tags of the repository
# in the current directory. The release workflow (.github/workflows/release.yaml)
# runs it; it changes nothing.
#
#   release-version.sh next major|minor|patch     the next release
#   release-version.sh next rc major|minor|patch  the first candidate for it
#   release-version.sh next rc                    the next candidate
#   release-version.sh previous <same arguments>  the tag that version counts
#                                                 from, for its notes and its
#                                                 Sentry commits
#
# Releases are x.y.z, release candidates x.y.z-rcN. Bumps count from the last
# release, not from a candidate: after 2.0.0-rc2, `major` is 2.0.0.

set -euo pipefail

VERSION_REGEXP='([0-9]+)\.([0-9]+)\.([0-9]+)(-rc([0-9]+))?'

# The version tags, newest first, by version: 1.10.0 after 1.9.0, and a
# release after its candidates (2.0.0 after 2.0.0-rc2). Tags in other forms
# are left out.
version_tags() {
    git -c versionsort.suffix=-rc tag --sort=-version:refname | grep -E "^$VERSION_REGEXP$" || true
}

fail() {
    echo "$1" >&2
    exit 1
}

COMMAND=${1:-}
MODE=${2:-}
BUMP=${3:-}

TAGS=$(version_tags)
LAST_RELEASE=$(echo "$TAGS" | grep -vE -- '-rc[0-9]+$' | head -n 1 || true)
LATEST=$(echo "$TAGS" | head -n 1)

case "$MODE" in
    major|minor|patch) [ -z "$BUMP" ] || fail "'$MODE' takes no second argument." ;;
    rc) case "$BUMP" in ''|major|minor|patch) ;; *) fail "Unknown release type 'rc $BUMP'." ;; esac ;;
    *) fail "Unknown release type '$MODE'; use major, minor, patch or rc." ;;
esac

case "$COMMAND" in
    next|previous) ;;
    *) fail "Usage: release-version.sh next|previous major|minor|patch|rc [major|minor|patch]" ;;
esac

# Sets VERSION, and PREVIOUS, the tag it counts from: the candidate before it
# for a later candidate, else the last release.
if [ "$MODE" = rc ] && [ -z "$BUMP" ]; then
    if [[ ! $LATEST =~ ^$VERSION_REGEXP$ ]] || [ -z "${BASH_REMATCH[5]}" ]; then
        fail "There is no release candidate to continue; start one with 'rc major', 'rc minor' or 'rc patch'."
    fi
    VERSION="${BASH_REMATCH[1]}.${BASH_REMATCH[2]}.${BASH_REMATCH[3]}-rc$((BASH_REMATCH[5] + 1))"
    PREVIOUS=$LATEST
else
    [[ $LAST_RELEASE =~ ^$VERSION_REGEXP$ ]] || fail "There is no release tag in the format x.y.z to count from."

    MAJOR=${BASH_REMATCH[1]} MINOR=${BASH_REMATCH[2]} PATCH=${BASH_REMATCH[3]}

    case ${BUMP:-$MODE} in
        major) MAJOR=$((MAJOR + 1)); MINOR=0; PATCH=0 ;;
        minor) MINOR=$((MINOR + 1)); PATCH=0 ;;
        patch) PATCH=$((PATCH + 1)) ;;
    esac

    VERSION="$MAJOR.$MINOR.$PATCH"
    PREVIOUS=$LAST_RELEASE

    if [ "$MODE" = rc ]; then
        # Counts on from an earlier candidate for the same release.
        PREV_RC=$(echo "$TAGS" | grep -E "^${VERSION//./\\.}-rc[0-9]+$" | head -n 1 || true)
        RC=1
        if [ -n "$PREV_RC" ]; then
            RC=$((${PREV_RC##*-rc} + 1))
            PREVIOUS=$PREV_RC
        fi
        VERSION="$VERSION-rc$RC"
    fi
fi

if [ "$COMMAND" = previous ]; then echo "$PREVIOUS"; else echo "$VERSION"; fi
