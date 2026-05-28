<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('ai', [
        'platform' => [
            'vertexai' => [
                'project_id' => '%env(VERTEX_AI_PROJECT_ID)%',
                'location' => '%env(VERTEX_AI_LOCATION)%',
            ],
            'generic' => [
                'antiseche' => [
                    'base_url' => '%env(ANTISECHE_BASE_URL)%',
                    'api_key' => '%env(ANTISECHE_API_KEY)%',
                    'model_catalog' => 'app.chatbot.antiseche.model_catalog',
                    'supports_embeddings' => false,
                ],
            ],
        ],
        'agent' => [
            'chatbot' => [
                'platform' => 'ai.platform.vertexai',
                'model' => [
                    'name' => 'gemini-3-flash-preview',
                    'options' => [
                        'stream' => true,
                    ],
                ],
            ],
            'antiseche' => [
                'platform' => 'ai.platform.generic.antiseche',
                'model' => [
                    'name' => 'antiseche-rag',
                    'options' => [
                        'stream' => true,
                    ],
                ],
            ],
        ],
    ]);
};
