#!/bin/bash

BASE="./base.sh";
ENV="production";
MSG="Production";
C="";

if [ "${PWD##*/}" != "scripts" ]
then
    cd "scripts";
fi

while getopts ":m:c:" o; do
    case "${o}" in
        m)
            MODE=${OPTARG}
            ;;
        c)
            CONF=${OPTARG}
            ;;
    esac
done

case "$MODE" in
    d)
        ENV="development";
        MSG="Development";
        ;;
    t)
        ENV="testing";
        MSG="Testing";
        ;;
    s)
        ENV="staging";
        MSG="Staging";
        ;;
esac

if [ -z "$CONF" ]
then
    C="-c";
fi

$BASE -t "grunt $ENV" -m "STARTING MONETIZATOR ($MSG Mode)" -e $ENV $C;
