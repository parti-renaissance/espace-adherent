FROM ubuntu:16.04

ENV LANG="en_US.UTF-8" \
    LC_ALL="en_US.UTF-8" \
    LANGUAGE="en_US.UTF-8" \
    TERM="xterm" \
    DEBIAN_FRONTEND="noninteractive" \
    COMPOSER_ALLOW_SUPERUSER=1

EXPOSE 80
WORKDIR /app

RUN apt-get update -q && \
    apt-get install -qy software-properties-common language-pack-en-base && \
    export LC_ALL=en_US.UTF-8 && \
    export LANG=en_US.UTF-8 && \
    add-apt-repository -y ppa:ondrej/php && \
    apt-get update -q && \
    apt-get install --no-install-recommends -qy \
        ca-certificates \
        cron \
        curl \
        nano \
        nginx \
        git \
        mysql-client \
        php7.1 \
        php7.1-bcmath \
        php7.1-common \
        php7.1-curl \
        php7.1-dom \
        php7.1-fpm \
        php7.1-gd \
        php7.1-iconv \
        php7.1-intl \
        php7.1-json \
        php7.1-mbstring \
        php7.1-mcrypt \
        php7.1-mysql \
        php7.1-opcache \
        php7.1-pdo \
        php7.1-phar \
        php7.1-xml \
        php7.1-zip \
        redis-server \
        supervisor \
        tzdata \
        wget && \

    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* && \

    cp /usr/share/zoneinfo/Europe/Paris /etc/localtime && echo "Europe/Paris" > /etc/timezone && \

    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . /app

RUN chmod 0444 gcloud-service-key.json && \

    mkdir /run/php && \
    mkdir var && \

    service redis-server start && \
    SYMFONY_ENV=prod REDIS_HOST=127.0.0.1 composer install --optimize-autoloader --no-interaction --no-ansi --no-dev && \
    service redis-server stop && \

    apt-get autoremove -y redis-server && \

    chown -R www-data:www-data var && \

    mv docker/prod/php.ini /etc/php/7.1/cli/conf.d/50-setting.ini && \
    rm -rf /etc/php/7.1/fpm/pool.d/www.conf && \
    mv docker/prod/pool.conf /etc/php/7.1/fpm/pool.d/www.conf && \
    rm -rf /etc/nginx/nginx.conf && \
    mv docker/prod/nginx.conf /etc/nginx/nginx.conf && \
    mv docker/prod/supervisord.conf /etc/supervisor/conf.d/ && \

    rm -rf docker && \
    rm -rf web/app_dev.php

CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
