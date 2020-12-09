FROM php:7.3-fpm-alpine AS en-marche_php

ENV APCU_VERSION 5.1.19
ENV AMQP_VERSION 1.10.2
ENV GOSU_VERSION 1.12

RUN apk add --no-cache \
		acl \
		fcgi \
		file \
		gettext \
		git \
		vim \
		nano \
	;

RUN set -eux; \
	apk add --no-cache --virtual .build-deps \
		$PHPIZE_DEPS \
		icu-dev \
		libzip-dev \
		zlib-dev \
		rabbitmq-c-dev \
		freetype-dev \
		libjpeg-turbo-dev \
        libjpeg-turbo \
        jpeg-dev \
        libpng-dev \
	; \
	\
	docker-php-ext-configure gd --with-png-dir=/usr/include/ --with-jpeg-dir=/usr/include/ --with-freetype-dir=/usr/include/; \
	docker-php-ext-configure zip; \
	docker-php-ext-install -j$(nproc) \
		intl \
		pdo_mysql \
		zip \
		gd \
		bcmath \
		exif \
	; \
	pecl install \
		apcu-${APCU_VERSION} \
        amqp-${AMQP_VERSION} \
	; \
	pecl clear-cache; \
	docker-php-ext-enable \
		apcu \
		opcache \
		amqp \
	; \
	\
	runDeps="$( \
		scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
			| tr ',' '\n' \
			| sort -u \
			| awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
	)"; \
	apk add --no-cache --virtual .api-phpexts-rundeps $runDeps; \
	\
	apk del .build-deps

COPY --from=composer:1 /usr/bin/composer /usr/bin/composer

RUN apk add --no-cache \
    nginx \
    wkhtmltopdf \
    nodejs \
    nodejs-npm \
    yarn \
    curl \
    su-exec \
    python3 \
    make \
    g++

EXPOSE 80
WORKDIR /app

#ENV LANG="en_US.UTF-8" \
#    LC_ALL="en_US.UTF-8" \
#    LANGUAGE="en_US.UTF-8" \
#    TERM="xterm" \
#    DEBIAN_FRONTEND="noninteractive" \
#    NODE_VERSION=10.20.1 \
#    GOSU_VERSION=1.11 \
#    COMPOSER_ALLOW_SUPERUSER=1

#RUN apt-get update -q && \
#    apt-get install -qy software-properties-common language-pack-en-base build-essential && \
#    export LC_ALL=en_US.UTF-8 && \
#    export LANG=en_US.UTF-8 && \
#    add-apt-repository ppa:ondrej/php && \
#    apt-get update -q && \
#    apt-get install --no-install-recommends -qy \
#        ca-certificates \
#        cron \
#        curl \
#        nano \
#        vim \
#        nginx \
#        git \
#        graphviz \
#        mysql-client \
#        php7.3 \
#        php7.3-bcmath \
#        php7.3-common \
#        php7.3-curl \
#        php7.3-dom \
#        php7.3-fpm \
#        php7.3-gd \
#        php7.3-iconv \
#        php7.3-intl \
#        php7.3-json \
#        php7.3-mbstring \
#        php7.3-mysql \
#        php7.3-pdo \
#        php7.3-phar \
#        php7.3-sqlite \
#        php7.3-xdebug \
#        php7.3-xml \
#        php7.3-zip \
#        php7.3-amqp \
#        php-apcu \
#        php-uuid \
#        php-imagick \
#        ghostscript \
#        supervisor \
#        tzdata \
#        wget \
#        wkhtmltopdf && \
#
#    phpdismod xdebug && \
#
#    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* && \
#
#    sed -i -e "s/<policy domain=\"coder\" rights=\"none\" pattern=\"PDF\" \/>/<policy domain=\"coder\" rights=\"read|write\" pattern=\"PDF\" \/>/g" /etc/ImageMagick-6/policy.xml && \
#
#    cp /usr/share/zoneinfo/Europe/Paris /etc/localtime && echo "Europe/Paris" > /etc/timezone && \
#
#    mkdir /run/php

RUN curl -L https://github.com/tianon/gosu/releases/download/${GOSU_VERSION}/gosu-$(dpkg --print-architecture) --output /usr/local/bin/gosu && \
    chmod +x /usr/local/bin/gosu && \
    addgroup enmarche && \
    adduser -h /home/enmarche -s /bin/bash -G enmarche -D enmarche

RUN ln -s $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini

COPY ./files/. /

ENTRYPOINT ["entrypoint.sh"]
