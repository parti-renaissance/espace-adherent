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
        postgresql-client \
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
        php7.1-pgsql \
        php7.1-opcache \
        php7.1-pdo \
        php7.1-phar \
        php7.1-xml \
        php7.1-zip \
        php7.1-amqp \
        php-apcu \
        php-uuid \
        supervisor \
        tzdata \
        wget \
        wkhtmltopdf && \

    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* && \

    cp /usr/share/zoneinfo/Europe/Paris /etc/localtime && echo "Europe/Paris" > /etc/timezone && \

    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \

    # Blackfire
    wget -O - https://packages.blackfire.io/gpg.key | apt-key add - \
    && echo "deb http://packages.blackfire.io/debian any main" | tee /etc/apt/sources.list.d/blackfire.list \
    && apt-get update -q \
    && apt-get install -qy blackfire-agent \
    && version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") \
    && curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/linux/amd64/$version \
    && tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp \
    && mv /tmp/blackfire-*.so $(php -r "echo ini_get('extension_dir');")/blackfire.so \
    && printf "extension=blackfire.so\nblackfire.agent_socket=tcp://\${BLACKFIRE_HOST}:8707\n" > /etc/php/7.1/cli/conf.d/blackfire.ini \
    && printf "extension=blackfire.so\nblackfire.agent_socket=tcp://\${BLACKFIRE_HOST}:8707\n" > /etc/php/7.1/fpm/conf.d/blackfire.ini

COPY . /app

COPY docker/prod/entrypoint.sh /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

RUN chmod 0444 gcloud-service-key.json && \

    mkdir /run/php && \
    mkdir var && \

    SYMFONY_ENV=prod composer install --optimize-autoloader --no-interaction --no-ansi --no-dev && \
    SYMFONY_ENV=prod bin/console cache:clear --no-warmup && \
    SYMFONY_ENV=prod bin/console cache:warmup && \

    chown -R www-data:www-data var && \

    cp docker/prod/php.ini /etc/php/7.1/cli/conf.d/50-setting.ini && \
    mv docker/prod/php.ini /etc/php/7.1/fpm/conf.d/50-setting.ini && \
    rm -rf /etc/php/7.1/fpm/pool.d/www.conf && \
    mv docker/prod/pool.conf /etc/php/7.1/fpm/pool.d/www.conf && \
    rm -rf /etc/nginx/nginx.conf && \
    mv docker/prod/nginx.conf /etc/nginx/nginx.conf && \
    mv docker/prod/supervisord.conf /etc/supervisor/conf.d/ && \

    rm -rf docker composer.lock

    CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
