<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('cache.adapter.null', Symfony\Component\Cache\Adapter\NullAdapter::class)
        ->args([
            null,
        ]);

    $containerConfigurator->extension('framework', [
        'test' => true,
        'session' => [
            'storage_factory_id' => 'session.storage.factory.mock_file',
        ],
        'cache' => [
            'app' => 'cache.adapter.apcu',
        ],
    ]);
};
