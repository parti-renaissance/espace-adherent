<?php

declare(strict_types=1);

use App\Sentry\Webhook\Routing\SentryEventRouter;

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    foreach ([
        'SLACK_CHANNEL_BACKEND_PHP_STAGING', 'SLACK_CHANNEL_BACKEND_PHP_PRODUCTION',
        'CLICKUP_CHANNEL_MOBILE_STAGING', 'CLICKUP_CHANNEL_MOBILE_PRODUCTION',
        'CLICKUP_CHANNEL_BACKEND_PHP_STAGING', 'CLICKUP_CHANNEL_BACKEND_PHP_PRODUCTION',
        'CLICKUP_CHANNEL_BACKEND_JS_STAGING', 'CLICKUP_CHANNEL_BACKEND_JS_PRODUCTION',
    ] as $channelEnvVar) {
        $parameters->set('env('.$channelEnvVar.')', '');
    }

    $services = $containerConfigurator->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire()
    ;

    $services->set(SentryEventRouter::class)
        ->arg('$routingTable', [
            'mobile' => [
                'staging' => ['clickup' => '%env(CLICKUP_CHANNEL_MOBILE_STAGING)%'],
                'production' => ['clickup' => '%env(CLICKUP_CHANNEL_MOBILE_PRODUCTION)%'],
            ],
            'backend-php' => [
                'staging' => ['slack' => '%env(SLACK_CHANNEL_BACKEND_PHP_STAGING)%', 'clickup' => '%env(CLICKUP_CHANNEL_BACKEND_PHP_STAGING)%'],
                'production' => ['slack' => '%env(SLACK_CHANNEL_BACKEND_PHP_PRODUCTION)%', 'clickup' => '%env(CLICKUP_CHANNEL_BACKEND_PHP_PRODUCTION)%'],
            ],
            'backend-js' => [
                'staging' => ['clickup' => '%env(CLICKUP_CHANNEL_BACKEND_JS_STAGING)%'],
                'production' => ['clickup' => '%env(CLICKUP_CHANNEL_BACKEND_JS_PRODUCTION)%'],
            ],
        ])
        ->arg('$mobileProjectId', '%env(SENTRY_PROJECT_MOBILE)%')
        ->arg('$backendProjectId', '%env(SENTRY_PROJECT_BACKEND)%')
    ;
};
