# syntax=docker/dockerfile:1.4
# Adapted from https://github.com/dunglas/symfony-docker

ARG PHP_VERSION=8.4
ARG NODE_VERSION=20

FROM node:${NODE_VERSION}-alpine AS node
RUN apk add --no-cache git
ENV COREPACK_ENABLE_DOWNLOAD_PROMPT=0
RUN corepack enable
WORKDIR /app

FROM mlocati/php-extension-installer:2.9 AS php_extension_installer

FROM dunglas/frankenphp:1-php${PHP_VERSION}-alpine AS frankenphp_base

ENV TZ Europe/Paris
ENV LANG fr_FR.UTF-8
ENV LANGUAGE fr_FR.UTF-8
ENV LC_ALL fr_FR.UTF-8

ARG BUILD_DEV

WORKDIR /app

# php extensions installer: https://github.com/mlocati/docker-php-extension-installer
COPY --link --from=php_extension_installer /usr/bin/install-php-extensions /usr/local/bin/

# persistent / runtime deps
RUN apk add --no-cache \
        acl \
        file \
        gettext \
        git \
        unzip \
        libzip-dev \
    ;

RUN set -eux; \
    install-php-extensions \
        apcu \
        intl \
        opcache \
        mbstring \
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

COPY --link docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH="${PATH}:/root/.composer/vendor/bin"

COPY --link --from=composer/composer:2-bin /composer /usr/bin/composer
COPY --link . .

RUN mkdir -p var/cache var/log

RUN test -z "$BUILD_DEV" && ( \
        set -eux; \
        composer install --prefer-dist --no-dev --no-progress --no-scripts --no-interaction; \
        composer dump-autoload --classmap-authoritative --no-dev; \
        composer dump-env prod; \
        composer run-script --no-dev post-install-cmd; \
        rm -rf /root/.composer; \
        chmod +x bin/console; sync \
    ) || :

RUN chown -R www-data:www-data var/

COPY --link docker/frankenphp/Caddyfile /etc/frankenphp/Caddyfile

EXPOSE 80

ENTRYPOINT ["docker-entrypoint"]

CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
