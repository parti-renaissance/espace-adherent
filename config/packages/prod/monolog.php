<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('monolog', [
        'handlers' => [
            'main' => [
                'type' => 'fingers_crossed',
                'channels' => [
                    '!deprecation',
                ],
                'action_level' => 'error',
                'handler' => 'sentry',
                'excluded_http_codes' => [
                    400,
                    403,
                    404,
                    405,
                ],
            ],
            'sentry_logs' => [
                'type' => 'service',
                'id' => Sentry\SentryBundle\Monolog\LogsHandler::class,
            ],
            'sentry_breadcrumbs' => [
                'type' => 'service',
                'channels' => [
                    '!deprecation',
                ],
                'name' => 'sentry_breadcrumbs',
                'id' => Sentry\Monolog\BreadcrumbHandler::class,
            ],
            'sentry' => [
                'type' => 'sentry',
                'level' => Monolog\Level::Error->value,
                'hub_id' => Sentry\State\HubInterface::class,
                'fill_extra_context' => true,
                'process_psr_3_messages' => false,
            ],
        ],
    ]);
};
