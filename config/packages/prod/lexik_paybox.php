<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('lexik_paybox', [
        'parameters' => [
            'production' => '%env(bool:PAYBOX_PRODUCTION)%',
        ],
    ]);
};
