<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\ErrorHandler;

require \dirname(__DIR__).'/vendor/autoload.php';

// https://github.com/symfony/symfony/issues/53812
set_exception_handler([new ErrorHandler(), 'handleException']);

if (file_exists(\dirname(__DIR__).'/config/bootstrap.php')) {
    require \dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    new Dotenv()->bootEnv(\dirname(__DIR__).'/.env');
}
