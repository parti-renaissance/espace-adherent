<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('lexik_paybox', [
        'parameters' => [
            'production' => false,
            'site' => '%env(PAYBOX_SITE)%',
            'rank' => '%env(PAYBOX_RANK)%',
            'login' => '%env(PAYBOX_IDENTIFIER)%',
            'hmac' => [
                'key' => '%env(PAYBOX_KEY)%',
            ],
        ],
    ]);
};
