<?php

declare(strict_types=1);

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('bazinga_geocoder', [
        'providers' => [
            'google_maps' => [
                'factory' => Bazinga\GeocoderBundle\ProviderFactory\GoogleMapsFactory::class,
                'cache' => 'app.bazinga.geocoder_cache',
                'cache_lifetime' => 5356800,
                'cache_precision' => null,
                'options' => [
                    'api_key' => '%env(GMAPS_PRIVATE_API_KEY)%',
                ],
            ],
        ],
    ]);

    $services = $containerConfigurator->services();

    $services->set('app.bazinga.geocoder_cache', Symfony\Component\Cache\Psr16Cache::class)
        ->args([
            service('app.cache.geocoder'),
        ]);
};
