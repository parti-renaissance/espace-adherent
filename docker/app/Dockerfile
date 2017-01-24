FROM php:7.0-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        unzip \
        git \
        libicu52 \
        libmcrypt4 \
        libjpeg62-turbo \
        libfreetype6 \
        libmcrypt-dev \
        libgcrypt11-dev \
        libpng12-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libgmp-dev \
        libicu-dev \
        zlib1g-dev \
    && ln -s /usr/include/x86_64-linux-gnu/gmp.h /usr/include/gmp.h \
    && docker-php-ext-configure gmp --with-gmp=/usr/include/x86_64-linux-gnu/ \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install gmp mcrypt mbstring intl pdo_mysql zip opcache gd bcmath \
    && pecl install apcu-5.1.7 && docker-php-ext-enable apcu --ini-name 10-docker-php-ext-apcu.ini \
    && pecl install apcu_bc-1.0.3 && docker-php-ext-enable apc --ini-name 20-docker-php-ext-apcu.ini \
    && rm /usr/include/gmp.h \
    && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false -o APT::AutoRemove::SuggestsImportant=false $buildDeps \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Composer
RUN curl -k -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Config
COPY php.ini /usr/local/etc/php/conf.d/docker.ini
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

ENV SYMFONY_ALLOW_APPDEV=1

RUN a2enmod rewrite

WORKDIR /var/www
