<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('security', [
        'password_hashers' => [
            App\Entity\Administrator::class => [
                'algorithm' => 'md5',
                'encode_as_base64' => false,
                'iterations' => 0,
            ],
            App\Entity\Adherent::class => [
                'algorithm' => 'md5',
                'encode_as_base64' => false,
                'iterations' => 0,
            ],
        ],
    ]);
};
