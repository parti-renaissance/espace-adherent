<?php

declare(strict_types=1);

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

use Symfony\Component\HttpFoundation\Request;

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../app/autoload.php';

require __DIR__.'/../app/trusted_proxies.php';

$kernel = new AppKernel('prod', false);

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
