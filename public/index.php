<?php

use App\Kernel;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/config/bootstrap.php';

if ($_SERVER['APP_DEBUG']) {
    Debug::enable();
}

if ('prod' === $_SERVER['APP_ENV']) {
    if (!empty($_SERVER['BASIC_AUTH_USER']) && !empty($_SERVER['BASIC_AUTH_PASSWORD']) && !empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        if (!isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) || $_SERVER['PHP_AUTH_USER'] !== $_SERVER['BASIC_AUTH_USER'] || $_SERVER['PHP_AUTH_PW'] !== $_SERVER['BASIC_AUTH_PASSWORD']) {
            header('HTTP/1.0 401 Unauthorized');
            header('WWW-Authenticate: Basic realm="Password required"');

            echo 'Unauthorized';
            exit;
        }
    }

    if (!isset($_SERVER['HTTP_CF_RAY'])) {
        echo 'GoogleHC healthy';
        exit;
    }

    if ((bool) $_SERVER['ENABLE_MAINTENANCE']) {
        include __DIR__.'/maintenance.html';
        exit;
    }
}

require __DIR__.'/../config/trusted_proxies.php';

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
