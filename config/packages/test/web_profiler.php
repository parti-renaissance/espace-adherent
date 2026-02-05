<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('web_profiler', [
        'toolbar' => false,
        'intercept_redirects' => false,
    ]);

    $containerConfigurator->extension('framework', [
        'profiler' => [
            'enabled' => false,
            'collect' => false,
        ],
    ]);
};
