<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('nelmio_cors', [
        'paths' => [
            '^/(api|oauth/v2/token)' => [
                'allow_credentials' => true,
                'origin_regex' => true,
                'allow_origin' => [
                    '%env(CORS_ALLOW_ORIGIN)%',
                ],
                'allow_methods' => [
                    'GET',
                    'OPTIONS',
                    'POST',
                    'PUT',
                    'PATCH',
                    'DELETE',
                    'HEAD',
                ],
                'allow_headers' => [
                    'Content-Type',
                    'Accept',
                    'Authorization',
                    'X-App-Version',
                ],
                'expose_headers' => [
                    'Content-Disposition',
                ],
                'max_age' => 3600,
            ],
            '^/asset' => [
                'origin_regex' => true,
                'allow_origin' => [
                    '%env(CORS_ALLOW_ORIGIN)%',
                ],
                'allow_methods' => [
                    'GET',
                ],
                'max_age' => 3600,
            ],
        ],
    ]);
};
