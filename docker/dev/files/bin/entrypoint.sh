#!/usr/bin/env sh
set -e

uid=$(stat -c %u /app)
gid=$(stat -c %g /app)

sed -i "s/user = www-data/user = enmarche/g" /usr/local/etc/php-fpm.conf
sed -i "s/group = www-data/group = enmarche/g" /usr/local/etc/php-fpm.conf
sed -i -r "s/enmarche:x:[0-9]+:[0-9]+:/enmarche:x:$uid:$gid:/g" /etc/passwd
sed -i -r "s/enmarche:x:[0-9]+:/enmarche:x:$gid:/g" /etc/group

chown $uid:$gid /home/enmarche

PHP_FPM_PID='/var/run/php-fpm.pid'

wait_for_pid () {
    try=0

    while test $try -lt 35 ; do
        if [ ! -f "$1" ] ; then
            try=''
            break
        fi

        echo -n .
        try=`expr $try + 1`
        sleep 1
    done
}

clean_up () {
    echo "Killing $(cat $PHP_FPM_PID)"

    kill -QUIT `cat $PHP_FPM_PID`
    wait_for_pid $PHP_FPM_PID

    echo "Done!"
    exit 0
}

# If any command is provided, fall back to the default entrypoint
if [ "$#" -ne 0 ]; then
    exec su-exec enmarche "$@"
fi

echo -e "\n===== Testing PHP config\n"
php-fpm -v
php-fpm -t

if command -v nginx > /dev/null; then
    echo -e "\n===== Testing NGINX config\n"
    nginx -t
fi

echo -e "\n===== Running FPM only for localhost\n"
php-fpm -d listen=127.0.0.1:9000 -D

echo -e "\n===== Creating signal listener\n"
trap clean_up QUIT TERM INT

echo -e "\n===== Starting NGINX\n"
nginx

echo -e "\n===== Starting FMP\n"
php-fpm -F --pid $PHP_FPM_PID &

while true; do sleep 1; done
