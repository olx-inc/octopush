#!/usr/bin/env bash

composer install

echo 'Running Unit tests'

phpunit
