<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('kreait_firebase', [
        'projects' => [
            'jemarche_app' => [
                'default' => true,
                'public' => false,
            ],
        ],
    ]);
};
