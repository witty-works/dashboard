#!/bin/bash
# Whether CI (.github/workflows/ci.yaml) passed on a commit: its latest run
# for the commit, pushed to a branch, completed and successful, with the jobs
# main requires among its successful ones. The release workflow runs it before
# it tags. Needs gh with a token that may read Actions, and GITHUB_REPOSITORY.
#
#   ci-passed.sh <commit sha>

set -euo pipefail

SHA=$1
REQUIRED=(php js browser)

RUN=$(gh run list --repo "$GITHUB_REPOSITORY" --workflow ci.yaml --commit "$SHA" --event push \
    --limit 1 --json databaseId,status,conclusion \
    --jq '.[0] // empty | "\(.databaseId) \(.status) \(.conclusion)"')
read -r ID STATE CONCLUSION <<< "$RUN" || true

if [ -z "${ID:-}" ]; then
    echo "CI has not run on $SHA; push it to main first." >&2
    exit 1
fi
if [ "$STATE" != completed ] || [ "$CONCLUSION" != success ]; then
    echo "CI's latest run on $SHA is $STATE ($CONCLUSION); wait for it, or fix main." >&2
    exit 1
fi

JOBS=$(gh run view "$ID" --repo "$GITHUB_REPOSITORY" --json jobs --jq '.jobs[] | "\(.name) \(.conclusion)"')
for JOB in "${REQUIRED[@]}"; do
    if ! grep -qx "$JOB success" <<< "$JOBS"; then
        echo "CI's '$JOB' did not pass in run $ID on $SHA." >&2
        exit 1
    fi
done

echo "CI passed on $SHA (run $ID)."
