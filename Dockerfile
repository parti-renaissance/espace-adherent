# syntax=docker/dockerfile:1.4
# Adapted from https://github.com/dunglas/symfony-docker

ARG CADDY_VERSION=2
ARG PHP_VERSION=8.1
ARG NODE_VERSION=16
#ARG BUILD_DEV

FROM node:${NODE_VERSION}-alpine AS node
RUN apk add --no-cache git
WORKDIR /srv/app

FROM caddy:${CADDY_VERSION} AS caddy

FROM mlocati/php-extension-installer:2 AS php_extension_installer

FROM php:${PHP_VERSION}-fpm-alpine AS php_caddy

#ARG BUILD_DEV

WORKDIR /srv/app

# php extensions installer: https://github.com/mlocati/docker-php-extension-installer
COPY --from=php_extension_installer --link /usr/bin/install-php-extensions /usr/local/bin/

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
        pdo \
        pdo_mysql \
        amqp \
        zip \
        sockets \
    ;

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY --link docker/php/conf.d/app.ini $PHP_INI_DIR/conf.d/
COPY --link docker/php/conf.d/app.prod.ini $PHP_INI_DIR/conf.d/

COPY --link docker/php/php-fpm.d/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf
RUN mkdir -p /var/run/php

COPY --link docker/php/docker-healthcheck.sh /usr/local/bin/docker-healthcheck
RUN chmod +x /usr/local/bin/docker-healthcheck

HEALTHCHECK --interval=10s --timeout=3s --retries=3 CMD ["docker-healthcheck"]

COPY --link docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

COPY --from=composer:lts /usr/bin/composer /usr/bin/composer

#RUN test -z "$BUILD_DEV" || (echo "" > $PHP_INI_DIR/conf.d/symfony.ini) && :
#
#VOLUME /var/run/php
#
#COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
#
## https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
#ENV COMPOSER_ALLOW_SUPERUSER=1
#
#ENV PATH="${PATH}:/root/.composer/vendor/bin"
#
#WORKDIR /srv/app
#
COPY . .
#
#RUN test -z "$BUILD_DEV" && ( \
#        set -eux; \
#        mkdir -p var/cache var/log; \
#        composer install --prefer-dist --no-dev --no-progress --no-scripts --no-interaction; \
#        composer dump-autoload --classmap-authoritative --no-dev; \
#        composer symfony:dump-env prod; \
#        composer run-script --no-dev post-install-cmd; \
#        chmod +x bin/console; sync \
#    ) || :
#
#VOLUME /srv/app/var
#
COPY --from=caddy /usr/bin/caddy /usr/bin/caddy
COPY docker/caddy/Caddyfile /etc/caddy/Caddyfile

EXPOSE 80

CMD ["multirun", "docker-entrypoint php-fpm -F -R", "caddy run --config /etc/caddy/Caddyfile --adapter caddyfile"]
