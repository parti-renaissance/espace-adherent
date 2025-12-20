#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
  set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
  if [ "$APP_ENV" = 'prod' ]; then
    rm -f var/cache/prod/url_matching_routes.*
    rm -f var/cache/prod/url_generating_routes.*
  fi
  setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX var/cache var/log
  setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX var/cache var/log
fi

exec docker-php-entrypoint "$@"
