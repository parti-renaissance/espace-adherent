<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->alias(League\Flysystem\FilesystemOperator::class, 'default.storage');

    $containerConfigurator->extension('flysystem', [
        'storages' => [
            'default.storage' => [
                'adapter' => 'local',
                'public_url' => 'http://%env(RENAISSANCE_HOST)%/assets',
                'options' => [
                    'directory' => '%kernel.project_dir%/app/data',
                ],
            ],
            'uploadable_file.storage' => [
                'adapter' => 'local',
                'public_url' => 'http://%env(RENAISSANCE_HOST)%/assets',
                'options' => [
                    'directory' => '%kernel.project_dir%/app/data/uploads/',
                ],
            ],
        ],
    ]);
};
