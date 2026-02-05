<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('flysystem', [
        'storages' => [
            'memory.storage' => [
                'adapter' => 'memory',
            ],
            'uploadable_file.storage' => [
                'adapter' => 'memory',
            ],
            'national_event.storage' => [
                'adapter' => 'memory',
            ],
            'public_user_file.storage' => [
                'adapter' => 'memory',
            ],
        ],
    ]);
};
