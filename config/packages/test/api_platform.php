<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('api_platform', [
        'defaults' => [
            'pagination_items_per_page' => 2,
        ],
    ]);
};
