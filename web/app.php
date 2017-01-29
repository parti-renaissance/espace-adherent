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

use Symfony\Component\HttpFoundation\Request;

require __DIR__.'/../app/trusted_proxies.php';

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../app/autoload.php';
include_once __DIR__.'/../var/bootstrap.php.cache';

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
