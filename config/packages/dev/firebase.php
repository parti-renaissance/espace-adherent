<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('kreait_firebase', [
        'projects' => [
            'jemarche_app' => [
                'credentials' => '%kernel.project_dir%/tests/Fixtures/gcloud-service-key.json',
            ],
        ],
    ]);
};
