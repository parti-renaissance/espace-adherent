# syntax=docker/dockerfile:1.4
# Adapted from https://github.com/dunglas/symfony-docker

ARG PHP_VERSION=8.5

FROM mlocati/php-extension-installer:2.11 AS php_extension_installer

FROM dunglas/frankenphp:1-php${PHP_VERSION} AS frankenphp_base

ENV TZ=Europe/Paris \
    LANG=C.UTF-8 \
    LC_ALL=C.UTF-8

ARG BUILD_DEV
ARG COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /srv/app

# php extensions installer: https://github.com/mlocati/docker-php-extension-installer
COPY --link --from=php_extension_installer /usr/bin/install-php-extensions /usr/local/bin/

# persistent / runtime deps
RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
    --mount=type=cache,target=/var/lib/apt,sharing=locked \
    rm -f /etc/apt/apt.conf.d/docker-clean \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        acl \
        file \
        gettext \
        git \
        unzip \
        libzip-dev \
        tzdata \
    && ln -snf /usr/share/zoneinfo/$TZ /etc/localtime \
    && echo $TZ > /etc/timezone

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

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY --link docker/php/conf.d/default.ini $PHP_INI_DIR/conf.d/
COPY --link docker/php/conf.d/app.prod.ini $PHP_INI_DIR/conf.d/app.ini

COPY --link --chmod=755 docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint

ENV PATH="${PATH}:/root/.composer/vendor/bin"

COPY --link --from=composer/composer:2-bin /composer /usr/bin/composer

# Composer deps first (cached layer, invalidated only when composer.* changes).
# opcache.preload is disabled here because /srv/app/config/preload.php is not
# in the image yet (the rest of the sources is copied below).
COPY --link composer.json composer.lock symfony.lock ./
RUN --mount=type=cache,target=/root/.composer/cache \
    if [ -z "$BUILD_DEV" ]; then \
        set -eux; \
        php -d opcache.preload= /usr/bin/composer install --prefer-dist --no-dev --no-progress --no-scripts --no-autoloader --no-interaction; \
    fi

# Then the rest of the app
COPY --link . .

RUN mkdir -p var/cache var/log

RUN --mount=type=cache,target=/root/.composer/cache \
    if [ -z "$BUILD_DEV" ]; then \
        set -eux; \
        composer dump-autoload --classmap-authoritative --no-dev; \
        composer dump-env prod; \
        composer run-script --no-dev post-install-cmd; \
        chmod +x bin/console; \
    fi

RUN chown -R www-data:www-data var/

COPY --link docker/frankenphp/Caddyfile /etc/frankenphp/Caddyfile

EXPOSE 80

ENTRYPOINT ["docker-entrypoint"]

CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
