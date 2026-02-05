<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('framework', [
        'notifier' => [
            'chatter_transports' => [
                'telegram' => '%env(REBOT_TELEGRAM_DSN)%',
            ],
        ],
    ]);
};
