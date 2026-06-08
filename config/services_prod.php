<?php

declare(strict_types=1);

use App\HttpClient\GoogleAuth\IdTokenHttpClient;
use App\HttpClient\GoogleAuth\IdTokenProvider;
use App\HttpClient\GoogleAuth\IdTokenProviderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(IdTokenProvider::class)
        ->arg('$cache', service('google_auth.id_token_cache'))
        ->arg('$logger', service('logger'));

    $services->alias(IdTokenProviderInterface::class, IdTokenProvider::class);

    $services->set(IdTokenHttpClient::class)
        ->decorate('timeline_indexer.client')
        ->args([
            service('.inner'),
            service(IdTokenProviderInterface::class),
            '%env(TIMELINE_INDEXER_URL)%',
            service('logger'),
        ]);

    $services->set('google_auth.timeline_ranker', IdTokenHttpClient::class)
        ->decorate('timeline_ranker.client')
        ->args([
            service('.inner'),
            service(IdTokenProviderInterface::class),
            '%env(TIMELINE_RANKER_URL)%',
            service('logger'),
        ]);
};
