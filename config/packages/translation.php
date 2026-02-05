<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('framework', [
        'default_locale' => '%locale%',
        'enabled_locales' => [
            '%locale%',
            'en',
        ],
        'translator' => [
            'paths' => [
                '%kernel.project_dir%/translations',
            ],
            'fallbacks' => [
                '%locale%',
            ],
        ],
    ]);
};
