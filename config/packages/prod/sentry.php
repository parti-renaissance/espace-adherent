<?php

declare(strict_types=1);

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(Sentry\Integration\FrameContextifierIntegration::class);

    $services->set(Sentry\Integration\RequestIntegration::class);

    $services->set(Sentry\SentryBundle\Monolog\LogsHandler::class)
        ->args([
            Monolog\Level::Info,
        ]);

    $services->set(Sentry\Monolog\BreadcrumbHandler::class)
        ->args([
            service(Sentry\State\HubInterface::class),
            Monolog\Level::Info,
        ]);

    $containerConfigurator->extension('sentry', [
        'dsn' => '%env(SENTRY_DSN)%',
        'register_error_listener' => false,
        'register_error_handler' => false,
        'tracing' => false,
        'messenger' => true,
        'options' => [
            'send_default_pii' => true,
            'default_integrations' => false,
            'ignore_exceptions' => [
                App\Sentry\SentryIgnoredExceptionInterface::class,
                ApiPlatform\Validator\Exception\ValidationException::class,
            ],
            'integrations' => [
                Sentry\Integration\FrameContextifierIntegration::class,
                Sentry\Integration\RequestIntegration::class,
            ],
            'environment' => '%env(APP_ENVIRONMENT)%',
            'release' => '%env(APP_VERSION)%',
            'enable_logs' => true,
        ],
    ]);
};
