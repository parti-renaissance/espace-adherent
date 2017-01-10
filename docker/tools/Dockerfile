FROM php:7.1-cli

ENV NODE_VERSION 6.0.0

RUN buildDeps=" \
        build-essential \
        libmcrypt-dev \
        libgcrypt11-dev \
        libpng12-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libgmp-dev \
        libicu-dev \
        zlib1g-dev \
    " \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        $buildDeps \
        vim \
        libicu52 \
        libmcrypt4 \
        libjpeg62-turbo \
        libfreetype6 \
        ssl-cert \
        graphviz \
    && ln -s /usr/include/x86_64-linux-gnu/gmp.h /usr/include/gmp.h \
    && docker-php-ext-configure intl \
    && docker-php-ext-configure gmp --with-gmp=/usr/include/x86_64-linux-gnu/ \
    && docker-php-ext-install gmp mcrypt mbstring intl pdo_mysql zip opcache \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install gd bcmath \
    && pecl install -f xdebug \
    && echo 'zend_extension=/usr/local/lib/php/extensions/no-debug-non-zts-20151012/xdebug.so' > $PHP_INI_DIR/conf.d/xdebug.ini \
    && pecl install apcu-5.1.7 && docker-php-ext-enable apcu --ini-name 10-docker-php-ext-apcu.ini \
    && pecl install apcu_bc-1.0.3 && docker-php-ext-enable apc --ini-name 20-docker-php-ext-apcu.ini \
    && rm /usr/include/gmp.h \
    && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false -o APT::AutoRemove::SuggestsImportant=false $buildDeps \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# PHP conf
COPY php.ini $PHP_INI_DIR/conf.d/z_docker.ini

RUN curl -k -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Directory of the application
WORKDIR /www

# NodeJS
RUN curl -L -o /tmp/nodejs.tar.gz https://nodejs.org/dist/v${NODE_VERSION}/node-v${NODE_VERSION}-linux-x64.tar.gz \
    && tar xfvz /tmp/nodejs.tar.gz -C /usr/local --strip-components=1 \
    && rm -r /tmp/nodejs.tar.gz \
    && npm install yarn -g
