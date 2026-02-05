<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('stof_doctrine_extensions', [
        'default_locale' => 'fr_FR',
        'orm' => [
            'default' => [
                'tree' => true,
                'sluggable' => true,
                'softdeleteable' => true,
                'timestampable' => true,
                'blameable' => true,
                'sortable' => true,
            ],
        ],
    ]);
};
