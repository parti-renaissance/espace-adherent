<?php

use App\Kernel;

if ('prod' === getenv('APP_ENV')) {
    if (!isset($_SERVER['HTTP_CF_RAY'])) {
        echo 'GoogleHC healthy';
        exit;
    }

    if (!empty($_SERVER['ENABLE_MAINTENANCE'])) {
        include __DIR__.'/maintenance.html';
        exit;
    }
}

require_once \dirname(__DIR__).'/vendor/autoload_runtime.php';

return fn(array $context) => new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
