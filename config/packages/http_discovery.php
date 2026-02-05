<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->alias(Psr\Http\Message\RequestFactoryInterface::class, 'http_discovery.psr17_factory');

    $services->alias(Psr\Http\Message\ResponseFactoryInterface::class, 'http_discovery.psr17_factory');

    $services->alias(Psr\Http\Message\ServerRequestFactoryInterface::class, 'http_discovery.psr17_factory');

    $services->alias(Psr\Http\Message\StreamFactoryInterface::class, 'http_discovery.psr17_factory');

    $services->alias(Psr\Http\Message\UploadedFileFactoryInterface::class, 'http_discovery.psr17_factory');

    $services->alias(Psr\Http\Message\UriFactoryInterface::class, 'http_discovery.psr17_factory');

    $services->set('http_discovery.psr17_factory', Http\Discovery\Psr17Factory::class);
};
