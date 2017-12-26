#!/bin/bash
set -e

if [[ "$1" = "supervisord" ]]; then
    rm -rf var/cache/*
    bin/console cache:clear --env=prod --no-warmup
    bin/console cache:warmup --env=prod
    chown -R www-data:www-data var
fi

exec "$@"
