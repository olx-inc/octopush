#!/bin/bash

BASE="./base.sh";
CMD="coverage";
NODE="";
INSTALL="";

if [ "${PWD##*/}" != "scripts" ]
then
    cd "scripts";
fi

while getopts ":t:ni" o; do
    case "${o}" in
        t)
            TEST=${OPTARG}
            ;;
        n)
            NODE="-n"
            ;;
        i)
            INSTALL="-i"
            ;;
    esac
done

case "$TEST" in
    u)
        CMD="coverage-unit"
        ;;
    i)
        CMD="coverage-integration"
        ;;
esac

echo "$BASE -t "grunt $CMD" -m "COVERING MONETIZATOR." -e "development" -c $NODE $INSTALL;"
$BASE -t "grunt $CMD" -m "COVERING MONETIZATOR." -e "development" -c $NODE $INSTALL;
