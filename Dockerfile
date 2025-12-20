# syntax=docker/dockerfile:1.4
# Adapted from https://github.com/dunglas/symfony-docker

ARG CADDY_VERSION=2
# Fix the version of PHP to avoid this bug https://github.com/php/php-src/issues/14480
ARG PHP_VERSION=8.4
ARG NODE_VERSION=20

FROM node:${NODE_VERSION}-alpine AS node
RUN apk add --no-cache git
ENV COREPACK_ENABLE_DOWNLOAD_PROMPT=0
RUN corepack enable
WORKDIR /srv/app

FROM caddy:${CADDY_VERSION}-alpine AS caddy

FROM mlocati/php-extension-installer:2.9 AS php_extension_installer

FROM php:${PHP_VERSION}-fpm-alpine AS php_caddy

ENV TZ Europe/Paris
ENV LANG fr_FR.UTF-8
ENV LANGUAGE fr_FR.UTF-8
ENV LC_ALL fr_FR.UTF-8

ENV PHP_FPM_MAX_CHILDREN=15 \
    PHP_FPM_START_SERVERS=4 \
    PHP_FPM_MIN_SPARE_SERVERS=2 \
    PHP_FPM_MAX_SPARE_SERVERS=6 \
    PHP_FPM_MAX_REQUESTS=100 \
    PHP_MEMORY_LIMIT=512M

ARG BUILD_DEV

WORKDIR /srv/app

# php extensions installer: https://github.com/mlocati/docker-php-extension-installer
COPY --link --from=php_extension_installer /usr/bin/install-php-extensions /usr/local/bin/

# persistent / runtime deps
RUN apk add --no-cache \
        acl \
        fcgi \
        file \
        gettext \
        git \
        multirun \
    ;

RUN set -eux; \
    install-php-extensions \
        apcu \
        intl \
        opcache \
        mbstring \
        exif \
        gd \
        bcmath \
        pdo \
        pdo_mysql \
        zip \
        redis \
        sockets \
    ;

RUN apk add --no-cache tzdata && \
    cp /usr/share/zoneinfo/Europe/Paris /etc/localtime && \
    echo "Europe/Paris" >  /etc/timezone && \
    apk del tzdata

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY --link docker/php/conf.d/default.ini $PHP_INI_DIR/conf.d/
COPY --link docker/php/conf.d/app.prod.ini $PHP_INI_DIR/conf.d/app.ini

COPY --link docker/php/php-fpm.d/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf
RUN mkdir -p /var/run/php

COPY --link docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH="${PATH}:/root/.composer/vendor/bin"

COPY --link --from=composer/composer:2-bin /composer /usr/bin/composer

COPY --link . .

RUN test -z "$BUILD_DEV" && ( \
        set -eux; \
        mkdir -p var/cache var/log; \
        composer install --prefer-dist --no-dev --no-progress --no-scripts --no-interaction; \
        composer dump-autoload --classmap-authoritative --no-dev; \
        composer dump-env prod; \
        composer run-script --no-dev post-install-cmd; \
        rm -rf /root/.composer; \
        chmod +x bin/console; sync \
    ) || :

COPY --link --from=caddy /usr/bin/caddy /usr/bin/caddy
COPY --link docker/caddy/Caddyfile /etc/caddy/Caddyfile

EXPOSE 80

CMD ["multirun", "docker-entrypoint php-fpm -F -R", "caddy run --config /etc/caddy/Caddyfile --adapter caddyfile"]
