#!/usr/bin/env bash

composer install

echo 'Running Unit tests'

./vendor/bin/phpunit
