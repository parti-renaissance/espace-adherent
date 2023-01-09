# Stage 1: Build the PHP project
FROM composer:2 as build
WORKDIR /app
COPY . /app
RUN composer install --optimize-autoloader --no-interaction --no-ansi --no-dev

# Stage 2: Create the final image
FROM ubuntu:20.04
ENV LANG="en_US.UTF-8" LC_ALL="en_US.UTF-8" LANGUAGE="en_US.UTF-8" TERM="xterm" PHP_VERSION=8.1 DEBIAN_FRONTEND="noninteractive" COMPOSER_ALLOW_SUPERUSER=1
EXPOSE 80
WORKDIR /app
RUN apt-get update -q && apt-get install -qy software-properties-common language-pack-en-base && export LC_ALL=en_US.UTF-8 && export LANG=en_US.UTF-8 && add-apt-repository -y ppa:ondrej/php && apt-get update -q && apt-get install --no-install-recommends -qy ca-certificates cron curl nano nginx git mysql-client php${PHP_VERSION} php${PHP_VERSION}-bcmath php${PHP_VERSION}-common php${PHP_VERSION}-curl php${PHP_VERSION}-dom php${PHP_VERSION}-fpm php${PHP_VERSION}-gd php${PHP_VERSION}-iconv php${PHP_VERSION}-intl php${PHP_VERSION}-mbstring php${PHP_VERSION}-mysql php${PHP_VERSION}-opcache php${PHP_VERSION}-pdo php${PHP_VERSION}-phar php${PHP_VERSION}-xml php${PHP_VERSION}-zip php${PHP_VERSION}-amqp php${PHP_VERSION}-apcu php${PHP_VERSION}-uuid php${PHP_VERSION}-imagick ghostscript supervisor tzdata wget && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* && sed -i -e "s/<policy domain=\"coder\" rights=\"none\" pattern=\"PDF\" \/>/<policy domain=\"coder\" rights=\"read|write\" pattern=\"PDF\" \/>/g" /etc/ImageMagick-6/policy.xml && cp /usr/share/zoneinfo/Europe/Paris /etc/localtime && echo "Europe/Paris" > /etc/timezone
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=build /app /app
COPY docker/prod/entrypoint.sh /usr/local/bin/entrypoint.sh
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
RUN mkdir /run/php && mkdir var && APP_ENV=prod composer install --optimize-autoloader --no-interaction --no-ansi --no-dev && APP_ENV=prod bin/console cache:clear --no-warmup && APP_ENV=prod bin/console cache:warmup
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
