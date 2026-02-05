<?php

declare(strict_types=1);

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__.'/services_dev.php');

    $parameters = $containerConfigurator->parameters();

    $parameters->set('ssl_encryption_key', '%env(SSL_ENCRYPTION_KEY)%');

    $parameters->set('ssl_private_key', '%env(SSL_PRIVATE_KEY)%');

    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $services->load('Tests\App\Behat\\', __DIR__.'/../tests/Behat/*')
        ->exclude([
            __DIR__.'/../tests/Behat/Context/JsonContext.php',
            __DIR__.'/../tests/Behat/Context/RestContext.php',
        ]);

    $services->alias(Doctrine\Bundle\FixturesBundle\Loader\SymfonyFixturesLoader::class, 'doctrine.fixtures.loader');

    $services->set(League\Glide\Server::class)
        ->factory([
            League\Glide\ServerFactory::class,
            'create',
        ])
        ->args([
            [
                'source' => service(League\Flysystem\FilesystemOperator::class),
                'cache' => service('memory.storage'),
                'response' => service(League\Glide\Responses\SymfonyResponseFactory::class),
                'max_image_size' => 4000000,
            ],
        ]);

    $services->alias('test.App\Adherent\Handler\RemoveAdherentAndRelatedDataCommandHandler', App\Adherent\Handler\RemoveAdherentAndRelatedDataCommandHandler::class);

    $services->alias('test.App\Vision\IdentityDocumentParser', App\Vision\IdentityDocumentParser::class);

    $services->alias(App\Image\ImageManagerInterface::class, Tests\App\Test\Image\DummyImageManager::class);

    $services->alias(App\Messenger\MessageRecorder\MessageRecorderInterface::class, App\Messenger\MessageRecorder\MessageRecorder::class);

    $services->set(Tests\App\HttpClient\MockHttpClientCallback::class);

    $services->set(Tests\App\Test\Image\DummyImageManager::class);

    $services->set(Tests\App\Controller\TestUXComponentController::class);

    $services->set(Tests\App\Test\Payment\PayboxProvider::class)
        ->args([
            '%env(SSL_PRIVATE_KEY)%',
        ]);
};
