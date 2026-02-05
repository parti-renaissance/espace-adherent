<?php

declare(strict_types=1);

return static function (Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->import('@FrameworkBundle/Resources/config/routing/errors.xml')
        ->prefix('/_error');

    $routingConfigurator->import(Tests\App\Controller\TestUXComponentController::class);
};
