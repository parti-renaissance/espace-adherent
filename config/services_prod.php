<?php

declare(strict_types=1);

use App\HttpClient\GoogleAuth\IdTokenHttpClient;
use App\HttpClient\GoogleAuth\IdTokenProvider;
use App\HttpClient\GoogleAuth\IdTokenProviderInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    // Plain in-memory ArrayAdapter — NOT a framework cache pool, so it is NOT tagged kernel.reset and
    // therefore survives across requests handled by the same FrankenPHP/Messenger worker: the ID token
    // is fetched once per ~55 min per pod, not on every request. Bearer secrets never reach Redis.
    $services->set('google_auth.id_token_cache', ArrayAdapter::class)
        ->autoconfigure(false);

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
