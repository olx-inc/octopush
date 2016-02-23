#!/bin/bash

echo "INSTALLING MONETIZATOR.";

REPO=${PWD};
MASTER="production";
VRS="v4.2.1";
NVM_DIR="$HOME/.nvm";
LOGS="/var/log/application"
CONF="../server/config/environment";
BUSINESS="../server/config/business";
V8="../server/modules/v8";
CV8="../server/modules/v8/build";
NODE_MODULES="node_modules";

if [ "${PWD##*/}" != "scripts" ]
then
    cd "scripts";
fi

SCRIPTS=${PWD};

if [ ! -d "$NVM_DIR" ]
then
    echo;
    echo "INSTALLING NVM";
    curl -o- https://raw.githubusercontent.com/creationix/nvm/v0.26.1/install.sh | bash
fi

[ -s "$NVM_DIR/nvm.sh" ] && . "$NVM_DIR/nvm.sh";  # This loads nvm

if [ ! "$(nvm ls $VRS | grep $VRS)" ]
then
    echo;
    echo "INSTALLING NODE.JS";
    nvm install $VRS;
fi

if [ ! -d "$LOGS" ]
then
    echo;
    echo "CREATING LOGS";
    sudo mkdir $LOGS;
    sudo chown -R $USER $LOGS;
fi

echo;
echo "INSTALLING NODE MODULES";
cd $REPO;
if [ -d "$NODE_MODULES" ]
then
    rm -rf $NODE_MODULES;
fi
nvm exec $VRS npm install
cd $SCRIPTS;

echo;
echo "INSTALLING CV8";
nvm exec $VRS npm i -g node-gyp;
if [ ! -d "$CV8" ]
then
    rm -rf $CV8;
fi
cd $V8;
node-gyp rebuild;
cd $SCRIPTS;
