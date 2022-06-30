ARG CADDY_VERSION=2
ARG PHP_VERSION=7.4
ARG NODE_VERSION=16
ARG APCU_VERSION=5.1.21
ARG BUILD_DEV

FROM node:${NODE_VERSION}-alpine AS node
RUN apk add --no-cache git
WORKDIR /srv/app

FROM caddy:${CADDY_VERSION} as caddy

FROM php:${PHP_VERSION}-fpm-alpine AS php_caddy

ARG BUILD_DEV
ARG APCU_VERSION

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
    apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-data-full \
        icu-dev \
        libzip-dev \
        zlib-dev \
        rabbitmq-c-dev \
        freetype libpng libjpeg-turbo freetype-dev libpng-dev libjpeg-turbo-dev \
    ; \
    \
    docker-php-ext-configure zip; \
    docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    ; \
    docker-php-ext-install -j$(nproc) \
        intl \
        sockets \
        pdo \
        pdo_mysql \
        gd \
        zip \
    ; \
    pecl install \
        apcu-${APCU_VERSION} \
        amqp \
    ; \
    pecl clear-cache; \
    docker-php-ext-enable \
        apcu \
        amqp \
        opcache \
        pdo_mysql \
    ; \
    \
    runDeps="$( \
        scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
            | tr ',' '\n' \
            | sort -u \
            | awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
    )"; \
    apk add --no-cache --virtual .phpexts-rundeps $runDeps; \
    \
    apk del .build-deps

COPY docker/php/docker-healthcheck.sh /usr/local/bin/docker-healthcheck
RUN chmod +x /usr/local/bin/docker-healthcheck

HEALTHCHECK --interval=10s --timeout=3s --retries=3 CMD ["docker-healthcheck"]

RUN ln -s $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini

COPY docker/php/conf.d/commun.ini $PHP_INI_DIR/conf.d/commun.ini
COPY docker/php/conf.d/symfony.prod.ini $PHP_INI_DIR/conf.d/symfony.ini
COPY docker/php/php-fpm.d/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf
COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint

RUN chmod +x /usr/local/bin/docker-entrypoint

RUN test -z "$BUILD_DEV" || (echo "" > $PHP_INI_DIR/conf.d/symfony.ini) && :

VOLUME /var/run/php

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

ENV PATH="${PATH}:/root/.composer/vendor/bin"

WORKDIR /srv/app

COPY . .

RUN test -z "$BUILD_DEV" && ( \
        set -eux; \
        mkdir -p var/cache var/log; \
        composer install --prefer-dist --no-dev --no-progress --no-scripts --no-interaction; \
        composer dump-autoload --classmap-authoritative --no-dev; \
        composer symfony:dump-env prod; \
        composer run-script --no-dev post-install-cmd; \
        chmod +x bin/console; sync \
    ) || :

VOLUME /srv/app/var

COPY --from=caddy /usr/bin/caddy /usr/bin/caddy
COPY docker/caddy/Caddyfile /etc/caddy/Caddyfile

EXPOSE 80
EXPOSE 443

#ENTRYPOINT ["docker-entrypoint"]

CMD ["multirun", "docker-entrypoint php-fpm -F -R", "caddy run --config /etc/caddy/Caddyfile --adapter caddyfile"]