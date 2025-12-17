#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
  set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
  if [ "$APP_ENV" = 'prod' ]; then
    rm -rf var/cache/*
    bin/console cache:clear --env=prod --no-warmup
    bin/console cache:warmup --env=prod
  fi
  setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX var/cache var/log
  setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX var/cache var/log
fi

exec docker-php-entrypoint "$@"
