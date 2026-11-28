#!/bin/bash
# Checks the versions release-version.sh computes, in throwaway repositories
# with the given tags. Needs only git and bash; CI runs it in the js job.
#
#   .github/scripts/release-version.test.sh

set -u

SCRIPT="$(cd "$(dirname "$0")" && pwd)/release-version.sh"
FAILED=0

# expect "<tags, oldest first>" "<arguments>" "<expected output or ERROR>"
expect() {
    local repo got
    repo=$(mktemp -d)
    got=$(
        cd "$repo"
        git init -q
        git -c user.name=t -c user.email=t@example.invalid commit -q --allow-empty -m init
        for tag in $1; do git tag "$tag"; done
        # The script must sort on its own, whatever tag.sort says.
        git config tag.sort refname
        "$SCRIPT" $2 2>/dev/null | tail -n 1
    )
    rm -rf "$repo"
    if [ "$3" = ERROR ]; then
        if [[ "$got" =~ ^[0-9]+\.[0-9]+\.[0-9]+ ]]; then
            echo "FAIL: [$1] $2: expected an error, got '$got'"; FAILED=1
        fi
    elif [ "$got" != "$3" ]; then
        echo "FAIL: [$1] $2: expected '$3', got '$got'"; FAILED=1
    fi
}

expect "1.42.8 1.42.9" "next major" "2.0.0"
expect "1.42.8 1.42.9" "next minor" "1.43.0"
expect "1.42.8 1.42.9" "next patch" "1.42.10"
# A first release candidate names the release it leads to.
expect "1.42.9" "next rc major" "2.0.0-rc1"
expect "1.42.9" "next rc minor" "1.43.0-rc1"
expect "1.42.9" "next rc patch" "1.42.10-rc1"
# The next one counts on.
expect "1.42.9 2.0.0-rc1" "next rc" "2.0.0-rc2"
expect "1.42.9 2.0.0-rc1 2.0.0-rc2" "next rc" "2.0.0-rc3"
expect "1.42.9 2.0.0-rc1" "next rc major" "2.0.0-rc2"
# After candidates, the release itself; bumps count from the last release.
expect "1.42.9 2.0.0-rc1 2.0.0-rc2" "next major" "2.0.0"
expect "1.42.9 2.0.0-rc1 2.0.0" "next patch" "2.0.1"
expect "1.42.9 2.0.0-rc1 2.0.0" "next rc minor" "2.1.0-rc1"
# Version order, not text order: 10 after 9, a release after its candidates.
expect "1.9.0 1.10.0" "next patch" "1.10.1"
expect "2.0.0-rc9 2.0.0-rc10" "next rc" "2.0.0-rc11"
# Without a candidate to continue, rc needs to know which release.
expect "1.42.9" "next rc" "ERROR"
expect "1.42.9 2.0.0-rc1 2.0.0" "next rc" "ERROR"
expect "1.42.9" "next rc huge" "ERROR"
expect "1.42.9" "next sideways" "ERROR"
# Tags in other forms are left out.
expect "1.42.9 1.42.9.1 v3 latest" "next patch" "1.42.10"

# What a new version counts from, for its notes and its Sentry commits: a
# release and a first candidate from the last release, so 2.0.0 lists
# everything since 1.42.9; a later candidate from the candidate before it.
expect "1.42.9 2.0.0-rc1 2.0.0-rc2" "previous major" "1.42.9"
expect "1.42.9 2.0.0-rc1 2.0.0-rc2" "previous rc" "2.0.0-rc2"
expect "2.0.0-rc9 2.0.0-rc10" "previous rc" "2.0.0-rc10"
expect "1.42.9" "previous rc major" "1.42.9"
expect "1.42.9 2.0.0-rc1" "previous rc major" "2.0.0-rc1"
expect "1.9.0 1.10.0" "previous patch" "1.10.0"
# An abandoned candidate for another release is not where a first one starts.
expect "1.42.9 1.43.0-rc1" "previous rc major" "1.42.9"
expect "1.42.9 1.43.0-rc1" "previous major" "1.42.9"

if [ "$FAILED" = 0 ]; then echo "All release-version checks passed."; fi
exit "$FAILED"
