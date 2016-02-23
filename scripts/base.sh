#!/bin/bash

REPO=${PWD};
ENV="production";
VRS="4.2.1";
NVM_DIR="$HOME/.nvm";
NODE_MODULES="../node_modules";
CONF_DIR="../server/config/environment";
BUSINESS_DIR="../server/config/business";
CMD="";

if [ "${PWD##*/}" != "scripts" ]
then
    cd "scripts";
fi

SCRIPTS=${PWD};

while getopts ":t:m:e:cni" o; do
    case "${o}" in
        t)
            TASK=${OPTARG}
            ;;
        m)
            MSG=${OPTARG}
            ;;
        e)
            ENV=${OPTARG}
            ;;
        c)
            CONF=1
            ;;
        n)
            NODE=1
            ;;
        i)
            INSTALL=1
            ;;
    esac
done

if [ -n "$INSTALL" ] && [ ! -d "$NODE_MODULES" ] || [ ! -d "$NVM_DIR" ] || [ ! "$(ls -A $CONF_DIR)" ] || [ ! "$(ls -A $BUSINESS_DIR)" ]
then
    ./install.sh;
fi

[ -s "$NVM_DIR/nvm.sh" ] && . "$NVM_DIR/nvm.sh"  # This loads nvm

echo;
echo $MSG;

if [ -z "$NODE" ]
then
    CMD="nvm exec $VRS";
fi

echo;
echo $CMD $TASK;
$CMD $TASK;
