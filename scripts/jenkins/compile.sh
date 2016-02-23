#!/usr/bin/env bash

composer install

zip -r ${BUILD_DIR}/${BUILD_FILE} . -x '.git/*' -x \*.zip -x 'test*' -x 'README.md' -x '/scripts*'
