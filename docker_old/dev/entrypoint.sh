#!/usr/bin/env bash
set -e

uid=$(stat -c %u /app)
gid=$(stat -c %g /app)

chown -R enmarche /app/var
chmod -R 775 /app/var

if [ $uid == 0 ] && [ $gid == 0 ]; then
    if [ $# -eq 0 ]; then
        supervisord -c /etc/supervisor/conf.d/supervisord.conf
    else
        exec "$@"
    fi
fi

sed -i "s/user = www-data/user = enmarche/g" /etc/php/7.4/fpm/pool.d/www.conf
sed -i "s/group = www-data/group = enmarche/g" /etc/php/7.4/fpm/pool.d/www.conf
sed -i -r "s/enmarche:x:[0-9]+:[0-9]+:/enmarche:x:$uid:$gid:/g" /etc/passwd
sed -i -r "s/enmarche:x:[0-9]+:/enmarche:x:$gid:/g" /etc/group

chown -R $uid:$gid /home/enmarche

#setfacl -R -m u:enmarche:rwX -m u:"$(whoami)":rwX /app/var
#setfacl -dR -m u:enmarche:rwX -m u:"$(whoami)":rwX /app/var

#chown -R www-data /app/var

if [ $# -eq 0 ]; then
    supervisord -c /etc/supervisor/conf.d/supervisord.conf
else
    exec gosu enmarche "$@"
fi
