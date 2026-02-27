#!/bin/sh
set -e

if [ "$1" = 'frankenphp' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
  if [ "$APP_ENV" = 'prod' ]; then
    rm -f var/cache/prod/url_matching_routes.*
    rm -f var/cache/prod/url_generating_routes.*
  fi
fi

exec docker-php-entrypoint "$@"
