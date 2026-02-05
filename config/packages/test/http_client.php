<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('framework', [
        'http_client' => [
            'mock_response_factory' => Tests\App\HttpClient\MockHttpClientCallback::class,
        ],
    ]);
};
