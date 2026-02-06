<?php

declare(strict_types=1);

return static function (Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator
        ->import('@WebProfilerBundle/Resources/config/routing/wdt.php')
        ->prefix('/_wdt');

    $routingConfigurator
        ->import('@WebProfilerBundle/Resources/config/routing/profiler.php')
        ->prefix('/_profiler');
};
