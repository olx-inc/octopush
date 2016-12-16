FROM quay.io/olx_inc/composer:5.5

ARG VERSION=dev

WORKDIR /app

ADD ./octopush-$VERSION.tar.gz /app/
