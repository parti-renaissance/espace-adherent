#!/usr/bin/env bash
set -e

uid=$(stat -c %u /app)
gid=$(stat -c %g /app)

if [ $uid == 0 ] && [ $gid == 0 ]; then
    if [ $# -eq 0 ]; then
        supervisord -c /etc/supervisor/conf.d/supervisord.conf
    else
        exec "$@"
    fi
fi

sed -i "s/user = www-data/user = enmarche/g" /etc/php/7.3/fpm/pool.d/www.conf
sed -i "s/group = www-data/group = enmarche/g" /etc/php/7.3/fpm/pool.d/www.conf
sed -i -r "s/enmarche:x:[0-9]+:[0-9]+:/enmarche:x:$uid:$gid:/g" /etc/passwd
sed -i -r "s/enmarche:x:[0-9]+:/enmarche:x:$gid:/g" /etc/group

chown $uid:$gid /home/enmarche

if [ $# -eq 0 ]; then
    supervisord -c /etc/supervisor/conf.d/supervisord.conf
else
    exec gosu enmarche "$@"
fi
