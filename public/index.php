<?php

use App\Kernel;

if ($_SERVER['ENABLE_MAINTENANCE']) {
    include __DIR__.'/maintenance.html';
    exit;
}

require_once \dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
