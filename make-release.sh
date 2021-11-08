#!/bin/bash

set -e;

VERSION_REGEXP='([0-9]+)\.([0-9]+)\.([0-9]+)';

case $1 in
    release|major|hotfix)
        MODE=$1;
        echo "Preparing $MODE, stashing changes and updating dev/main";

        git stash;
        git checkout dev;
        git pull --rebase;
        git checkout main;
        git pull --rebase;

        PREV_VERSION=$(git tag | head -n 1);

        REGEXP="^$VERSION_REGEXP+$";
        if [[ ! $PREV_VERSION =~ $REGEXP ]];
        then
            echo "Previous version should be in the format x.y.z and not '$PREV_VERSION'.";

            exit;
        fi

        declare -i MAJOR;
        declare -i MINOR;
        declare -i PATCH;

        case $MODE in
            major)
                MODE='release';
                MAJOR=${BASH_REMATCH[1]}+1;
                MINOR=0;
                PATCH=0;
            ;;
            release)
                MAJOR=${BASH_REMATCH[1]};
                MINOR=${BASH_REMATCH[2]}+1;
                PATCH=0;
            ;;
            hotfix)
                MAJOR=${BASH_REMATCH[1]};
                MINOR=${BASH_REMATCH[2]};
                PATCH=${BASH_REMATCH[3]}+1;
            ;;
        esac

        VERSION="$MAJOR.$MINOR.$PATCH";

        set -x
        git flow $MODE start $VERSION;
        sentry-cli releases new "$VERSION";
        sed "s/$PREV_VERSION/$VERSION/" config/sentry.php > config/sentry.php.bak
        mv config/sentry.php.bak config/sentry.php
        git stash pop;
    ;;

    finish)
        BRANCH=$(git branch --show-current);
        REGEXP="^(release|hotfix)/($VERSION_REGEXP)+$";

        if [[ ! $BRANCH =~ $REGEXP ]];
        then
            echo "Please make sure you are currently on release or hotfix branch, ie. a branch named 'release|hotfix/x.y.x'";

            exit;
        fi

        MODE=${BASH_REMATCH[1]};
        VERSION=${BASH_REMATCH[2]};

        set -x
        git flow $MODE finish $VERSION;
    ;;

    finalize)
        VERSION=$(git tag | head -n 1);
        PREV_VERSION=$(git tag | head -n 2 | tail -1);

        set -x
        git push origin main dev --tags;
        sentry-cli releases finalize "$VERSION";
        sentry-cli releases set-commits "$VERSION" --commit "Witty.works / recruit@$PREV_VERSION..$VERSION";
    ;;

    *)
        echo "./make-release.sh [release|major|hotfix|finish|finalize]";
   ;;
esac
