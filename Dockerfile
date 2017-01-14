FROM alpine:latest

ENV LANG="en_US.UTF-8" \
    LC_ALL="en_US.UTF-8" \
    LANGUAGE="en_US.UTF-8" \
    TERM="xterm"

COPY docker/run.sh /usr/local/bin/run.sh
COPY . /app

RUN echo "http://dl-4.alpinelinux.org/alpine/edge/main" > /etc/apk/repositories && \
    echo "http://dl-4.alpinelinux.org/alpine/edge/community" >> /etc/apk/repositories && \
    echo "http://dl-4.alpinelinux.org/alpine/edge/testing" >> /etc/apk/repositories && \
    apk --update add \
        curl \
        git \
        nginx \
        php7 \
        php7-bcmath \
        php7-curl \
        php7-ctype \
        php7-dom \
        php7-exif \
        php7-fpm \
        php7-gd \
        php7-iconv \
        php7-intl \
        php7-json \
        php7-mbstring \
        php7-mcrypt \
        php7-opcache \
        php7-openssl \
        php7-pdo \
        php7-pdo_mysql \
        php7-phar \
        php7-session \
        php7-xml \
        php7-zlib \
        php7-zip \
        tzdata \
    && rm -rf /var/cache/apk/* \
    && ln -s /usr/bin/php7 /usr/bin/php \
    && ln -s /usr/sbin/php-fpm7 /usr/bin/php-fpm \
    && cp /usr/share/zoneinfo/Europe/Paris /etc/localtime && echo "Europe/Paris" > /etc/timezone \
    && chmod a+x /usr/local/bin/run.sh \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer \
    && cd /app \
    && rm -rf docker \
    && SYMFONY_ENV=prod composer install --optimize-autoloader --no-interaction --no-ansi --no-dev \
    && chown -R nobody:nobody var \
    && chmod 0444 gcloud-service-key.json

COPY docker/php.ini /etc/php7/conf.d/50-setting.ini
COPY docker/www.conf /etc/php7/php-fpm.d/www.conf
COPY docker/nginx.conf /etc/nginx/nginx.conf

EXPOSE 80
WORKDIR /app

CMD ["/usr/local/bin/run.sh"]
