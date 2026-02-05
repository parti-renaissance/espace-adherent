<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('twig_component', [
        'anonymous_template_directory' => 'components/',
        'defaults' => [
            'App\Twig\Components\\' => 'components/',
        ],
    ]);
};
