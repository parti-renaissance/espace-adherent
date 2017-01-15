<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\NewsletterSubscription;
use AppBundle\DataFixtures\ORM\LoadHomeBlockData;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NewsletterControllerTest extends WebTestCase
{
    /**
     * @var Client
     */
    private $client;

    public function testSubscriptionAndRetry()
    {
        $entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $subscriptionsRepository = $entityManager->getRepository('AppBundle:NewsletterSubscription');

        // There should not be any subscription for the moment
        $this->assertEmpty($subscriptionsRepository->findAll());

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, '/newsletter');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=app_newsletter_subscription]')->form([
            'app_newsletter_subscription[email]' => 'titouan.galopin@en-marche.fr',
            'app_newsletter_subscription[postalCode]' => '10000',
        ]);

        $this->client->submit($form);

        // Subscription should have been saved
        $subscriptions = $subscriptionsRepository->findAll();
        $this->assertCount(1, $subscriptions);

        /** @var NewsletterSubscription $subscription */
        $subscription = $subscriptions[0];

        $this->assertEquals('titouan.galopin@en-marche.fr', $subscription->getEmail());
        $this->assertEquals('10000', $subscription->getPostalCode());

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Try another time with the same email (should fail)
        $crawler = $this->client->request(Request::METHOD_GET, '/newsletter');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=app_newsletter_subscription]')->form([
            'app_newsletter_subscription[email]' => 'titouan.galopin@en-marche.fr',
            'app_newsletter_subscription[postalCode]' => '20000',
        ]);

        $this->client->submit($form);

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Subscription should not have been saved
        $subscriptions = $subscriptionsRepository->findAll();
        $this->assertCount(1, $subscriptions);

        $entityManager->remove($subscriptions[0]);
        $entityManager->flush();
    }

    public function testSubscriptionFromHome()
    {
        $entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $subscriptionsRepository = $entityManager->getRepository('AppBundle:NewsletterSubscription');

        // There should not be any donation for the moment
        $this->assertEmpty($subscriptionsRepository->findAll());

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, '/');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=app_newsletter_subscription]')->form([
            'app_newsletter_subscription[email]' => 'titouan.galopin@en-marche.fr',
            'app_newsletter_subscription[postalCode]' => '10000',
        ]);

        $this->client->submit($form);

        // Subscription should have been saved
        $subscriptions = $subscriptionsRepository->findAll();
        $this->assertCount(1, $subscriptions);

        $entityManager->remove($subscriptions[0]);
        $entityManager->flush();
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadHomeBlockData::class,
        ]);

        $this->client = static::createClient();
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);

        $this->client = null;

        parent::tearDown();
    }
}
