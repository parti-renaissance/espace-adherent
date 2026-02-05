<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('vich_uploader', [
        'db_driver' => 'orm',
        'storage' => 'flysystem',
        'metadata' => [
            'type' => 'attribute',
        ],
        'mappings' => [
            'uploadable_file' => [
                'uri_prefix' => '/assets/uploads',
                'upload_destination' => 'uploadable_file.storage',
                'namer' => Vich\UploaderBundle\Naming\UniqidNamer::class,
                'directory_namer' => [
                    'service' => Vich\UploaderBundle\Naming\SubdirDirectoryNamer::class,
                ],
                'delete_on_update' => false,
                'delete_on_remove' => true,
            ],
        ],
    ]);
};
