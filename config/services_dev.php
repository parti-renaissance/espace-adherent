<?php

declare(strict_types=1);

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->bind('$environment', '%kernel.environment%');

    $services->load('App\DataFixtures\\', __DIR__.'/../src/DataFixtures/');

    $services->set(Tests\App\Controller\TestUXComponentController::class);

    $services->set(Tests\App\Test\Geocoder\DummyGeocoder::class);

    $services->alias(Geocoder\Geocoder::class, Tests\App\Test\Geocoder\DummyGeocoder::class);

    $services->set(Tests\App\GoCardless\DummyClient::class);

    $services->alias(App\GoCardless\ClientInterface::class, Tests\App\GoCardless\DummyClient::class);

    $services->set(Tests\App\Ohme\DummyClient::class);

    $services->alias(App\Ohme\ClientInterface::class, Tests\App\Ohme\DummyClient::class);

    $services->set(Tests\App\Chatbot\Provider\DummyProvider::class);

    $services->alias(App\Chatbot\Provider\ProviderInterface::class, Tests\App\Chatbot\Provider\DummyProvider::class);

    $services->set(App\Mandrill\EmailClient::class, Tests\App\Test\Mailer\NullEmailClient::class);

    $services->set(Tests\App\Test\Recaptcha\DummyRecaptchaApiClient::class);

    $services->set(App\Validator\RecaptchaValidator::class)
        ->arg('$apiClients', [
            service(Tests\App\Test\Recaptcha\DummyRecaptchaApiClient::class),
        ]);

    $services->set(Tests\App\Test\Algolia\DummySearchService::class)
        ->decorate(Algolia\SearchBundle\SearchService::class)
        ->args([
            service('.inner'),
        ]);

    $services->set(App\OpenGraph\OpenGraphFetcher::class, Tests\App\Test\OpenGraph\DummyOpenGraphFetcher::class);

    $services->set(Tests\App\Test\Firebase\DummyMessaging::class);

    $services->alias(Kreait\Firebase\Contract\Messaging::class, Tests\App\Test\Firebase\DummyMessaging::class);
};
