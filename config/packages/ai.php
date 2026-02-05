<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('ai', [
        'platform' => [
            'gemini' => [
                'api_key' => '%env(GEMINI_API_KEY)%',
            ],
        ],
        'agent' => [
            'default' => [
                'platform' => 'ai.platform.gemini',
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
