<?php

require __DIR__.'/../app/autoload.php';

echo 'Preparing database...';
passthru('rm -rf var/cache/test');
passthru('php bin/console doctrine:schema:drop --quiet -f --env=test_mysql');
passthru('php bin/console doctrine:schema:create --quiet --env=test_mysql');
echo " Done\n";
