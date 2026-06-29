<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('async_aws', [
        'clients' => [
            'ses' => [
                'type' => 'ses',
                'config' => [
                    'region' => '%env(AWS_REGION)%',
                ],
            ],
        ],
    ]);
};
