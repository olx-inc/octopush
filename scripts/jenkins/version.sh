#!/usr/bin/env bash

git describe --match=v*.*.* --tags ${STAGE} | cut -d 'v' -f 2;
