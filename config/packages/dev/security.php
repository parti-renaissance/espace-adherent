<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('security', [
        // Keep bcrypt to match prod hash format, but use the minimum cost
        'password_hashers' => [
            App\Entity\Administrator::class => ['algorithm' => 'bcrypt', 'cost' => 4],
            App\Entity\Adherent::class => ['algorithm' => 'bcrypt', 'cost' => 4],
        ],
    ]);
};
