<?php

declare(strict_types=1);

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(App\HtmlPurifier\HtmlPurifierProxy::class)
        ->lazy(true)
        ->decorate('exercise_html_purifier.event')
        ->args([
            service('.inner'),
        ]);

    $containerConfigurator->extension('exercise_html_purifier', [
        'html_profiles' => [
            'default' => [
                'config' => [
                    'Core.Encoding' => 'UTF-8',
                ],
            ],
            'event' => [
                'config' => [
                    'HTML.Allowed' => 'p,em,strong,ol,ul,li,br,a[href|title|rel|target]',
                ],
            ],
        ],
    ]);
};
