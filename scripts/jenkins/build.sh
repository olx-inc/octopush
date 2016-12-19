#!/usr/bin/env bash

composer install

composer exec phpunit

tar -vzcf ${BUILD_DIR}/${BUILD_FILE} \
      --exclude '.git/*' \
      --exclude '*.tar.gz' \
      --exclude 'test*' \
      --exclude 'README.md' \
      --exclude '/scripts*' \
      .
