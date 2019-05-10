<?php

use AppBundle\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

require __DIR__.'/../vendor/autoload.php';

if (getenv('APP_DEBUG')) {
    // Deny if client address is remote and is not in a container
    if (!in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'], true) && false === getenv('SYMFONY_ALLOW_APPDEV')) {
        header('HTTP/1.0 403 Forbidden');
        exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
    }

    Debug::enable();
}

if ('prod' === getenv('APP_ENV')) {
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

require __DIR__.'/../app/trusted_proxies.php';

$kernel = new Kernel(getenv('APP_ENV'), getenv('APP_DEBUG'));
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
