<?php

require __DIR__.'/../app/autoload.php';

passthru('rm -rf var/cache/test');
passthru('php bin/console doctrine:schema:create --quiet --env=test');
passthru('php bin/console app:content:prepare --quiet --env=test');
