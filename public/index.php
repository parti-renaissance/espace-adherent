<?php

use App\Kernel;

if ('prod' === getenv('APP_ENV')) {
    if (!isset($_SERVER['HTTP_CF_RAY'])) {
        echo 'GoogleHC healthy';
        exit;
    }

    if (isset($_SERVER['ENABLE_MAINTENANCE'])) {
        include __DIR__.'/maintenance.html';
        exit;
    }
}

require_once \dirname(__DIR__).'/vendor/autoload_runtime.php';
require_once \dirname(__DIR__).'/config/trusted_proxies.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
