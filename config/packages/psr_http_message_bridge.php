<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->alias(Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface::class, Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory::class);

    $services->alias(Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface::class, Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory::class);

    $services->set(Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory::class);

    $services->set(Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory::class);

    $services->set(Symfony\Bridge\PsrHttpMessage\ArgumentValueResolver\PsrServerRequestResolver::class);

    $services->set(Symfony\Bridge\PsrHttpMessage\EventListener\PsrResponseListener::class);
};
