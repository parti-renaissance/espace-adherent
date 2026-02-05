<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(Google\Cloud\Storage\StorageClient::class);

    $containerConfigurator->extension('flysystem', [
        'storages' => [
            'default.storage' => [
                'adapter' => 'gcloud',
                'options' => [
                    'client' => Google\Cloud\Storage\StorageClient::class,
                    'bucket' => '%env(GCLOUD_BUCKET)%',
                ],
            ],
            'uploadable_file.storage' => [
                'adapter' => 'gcloud',
                'options' => [
                    'client' => Google\Cloud\Storage\StorageClient::class,
                    'bucket' => '%env(GCLOUD_BUCKET)%',
                    'prefix' => 'uploads/',
                ],
            ],
            'national_event.storage' => [
                'adapter' => 'gcloud',
                'options' => [
                    'client' => Google\Cloud\Storage\StorageClient::class,
                    'bucket' => '%env(GCLOUD_NATIONAL_EVENT_BUCKET)%',
                    'visibility_handler' => 'flysystem.adapter.gcloud.visibility.uniform',
                ],
            ],
            'public_user_file.storage' => [
                'adapter' => 'gcloud',
                'public_url' => 'https://%env(GCLOUD_PUBLIC_USER_FILE_BUCKET)%/public',
                'options' => [
                    'client' => Google\Cloud\Storage\StorageClient::class,
                    'bucket' => '%env(GCLOUD_PUBLIC_USER_FILE_BUCKET)%',
                    'prefix' => 'public',
                    'visibility_handler' => 'flysystem.adapter.gcloud.visibility.uniform',
                ],
            ],
        ],
    ]);
};
