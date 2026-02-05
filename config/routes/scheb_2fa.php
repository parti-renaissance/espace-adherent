<?php

declare(strict_types=1);

return static function (Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->add('admin_security_2fa', '/login/2fa')
        ->host('%admin_renaissance_host%')
        ->controller([
            'scheb_two_factor.form_controller',
            'form',
        ]);

    $routingConfigurator->add('admin_security_2fa_check', '/login/2fa_check')
        ->host('%admin_renaissance_host%');
};
