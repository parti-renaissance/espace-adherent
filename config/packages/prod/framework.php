<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('framework', [
        'trusted_proxies' => '%env(TRUSTED_PROXIES)%',
        'trusted_headers' => [
            'x-forwarded-for',
            'x-forwarded-proto',
        ],
        'session' => [
            'cookie_secure' => true,
            'cookie_samesite' => 'lax',
            'cookie_httponly' => true,
        ],
    ]);
};
