<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('ai', [
        'platform' => [
            'vertexai' => [
                'project_id' => '%env(VERTEX_AI_PROJECT_ID)%',
                'location' => '%env(VERTEX_AI_LOCATION)%',
            ],
        ],
        'agent' => [
            'default' => [
                'platform' => 'ai.platform.vertexai',
                'model' => [
                    'name' => 'gemini-2.5-flash-lite',
                    'options' => [
                        'stream' => true,
                    ],
                ],
            ],
        ],
    ]);
};
