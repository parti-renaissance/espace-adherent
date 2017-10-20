#!/bin/bash
set -e

if [ "$SYMFONY_ENV" == "prod" ] then
    su www-data -s /bin/bash -c "bin/console cache:clear --no-warmup && bin/console cache:warmup"
fi

exec "$@"
