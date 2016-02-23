#!/bin/bash

CURRENT=$(git branch | grep "*" | cut -d ' ' -f 2)
OLD = "develop"
CONF="development";
NEW="update-config";
MSG="Updated config";

while getopts ":s:" o; do
    case "${o}" in
        s)
            STASH=1
            ;;
    esac
done

if [ -n "$STASH" ]
then
git stash;
fi
git checkout $OLD
git submodule foreach git checkout $CONFIG;
git submodule foreach git pull origin $CONFIG;
git branch -D $NEW;
git checkout -b $NEW;
git add -A;
git commit -am $MSG;
git checkout $OLD;
git merge $NEW;
git push origin $OLD;
git checkout $CURRENT;
if [ -n "$STASH" ]
then
git stash pop;
fi
