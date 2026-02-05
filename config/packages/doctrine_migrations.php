<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('doctrine_migrations', [
        'migrations_paths' => [
            'Migrations' => '%kernel.project_dir%/migrations',
        ],
        'enable_profiler' => false,
        'storage' => [
            'table_storage' => [
                'table_name' => 'migrations',
            ],
        ],
        'organize_migrations' => 'BY_YEAR',
        'custom_template' => '%kernel.project_dir%/migrations/migration.tpl',
    ]);
};
