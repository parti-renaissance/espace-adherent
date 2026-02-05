<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('doctrine', [
        'orm' => [
            'metadata_cache_driver' => [
                'type' => 'pool',
                'pool' => 'doctrine.metadata_cache_pool',
            ],
            'query_cache_driver' => [
                'type' => 'pool',
                'pool' => 'doctrine.query_cache_pool',
            ],
            'result_cache_driver' => [
                'type' => 'pool',
                'pool' => 'doctrine.result_cache_pool',
            ],
        ],
    ]);
};
