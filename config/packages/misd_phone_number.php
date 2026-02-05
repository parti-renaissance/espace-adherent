<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('misd_phone_number', [
        'validator' => [
            'default_region' => 'FR',
        ],
    ]);
};
