<?php

require __DIR__.'/../app/autoload.php';

echo 'Clearing cache...';
passthru('rm -rf var/cache/test');
passthru('rm -rf /tmp/data.db');
echo " Done\n";

echo 'Preparing SQLite database...';
passthru('php bin/console doctrine:schema:create --quiet --env=test_sqlite');
echo " Done\n";

echo 'Preparing MySQL database...';
passthru('php bin/console doctrine:schema:drop --quiet --force --env=test_mysql');
passthru('php bin/console doctrine:schema:create --quiet --env=test_mysql');
echo " Done\n";
