<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('lexik_paybox', [
        'public_key' => '%env(SSL_PUBLIC_KEY)%',
    ]);
};
