<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('mercure', [
        'hubs' => [
            'default' => [
                'url' => 'http://%env(MERCURE_HOST)%/.well-known/mercure',
                'public_url' => '/.well-known/mercure',
                'jwt' => [
                    'secret' => '%env(MERCURE_JWT_SECRET)%',
                    'publish' => '*',
                ],
            ],
        ],
    ]);
};
