<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Repository\NewsletterSubscriptionRepository;
use Symfony\Bundle\FrameworkBundle\Client;
use AppBundle\DataFixtures\ORM\LoadHomeBlockData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NewsletterControllerTest extends AbstractControllerTest
{
    /** @var Client */
    private $client;

    /** @var NewsletterSubscriptionRepository */
    private $subscriptionsRepository;

    public function testSubscriptionAndRetry()
    {
        // There should not be any subscription for the moment
        $this->assertCount(0, $this->subscriptionsRepository->findAll());

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, '/newsletter');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->client->submit($crawler->filter('form[name=app_newsletter_subscription]')->form([
            'app_newsletter_subscription[email]' => 'titouan.galopin@en-marche.fr',
            'app_newsletter_subscription[postalCode]' => '10000',
        ]));

        // Subscription should have been saved
        $this->assertCount(1, $subscriptions = $this->subscriptionsRepository->findAll());

        /** @var NewsletterSubscription $subscription */
        $subscription = $subscriptions[0];

        $this->assertSame('titouan.galopin@en-marche.fr', $subscription->getEmail());
        $this->assertSame('10000', $subscription->getPostalCode());
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Try another time with the same email (should fail)
        $crawler = $this->client->request(Request::METHOD_GET, '/newsletter');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->client->submit($crawler->filter('form[name=app_newsletter_subscription]')->form([
            'app_newsletter_subscription[email]' => 'titouan.galopin@en-marche.fr',
            'app_newsletter_subscription[postalCode]' => '20000',
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Subscription should not have been saved
        $this->assertCount(1, $subscriptions = $this->subscriptionsRepository->findAll());
    }

    public function testSubscriptionFromHome()
    {
        // There should not be any donation for the moment
        $this->assertCount(0, $this->subscriptionsRepository->findAll());

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, '/');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->client->submit($crawler->filter('form[name=app_newsletter_subscription]')->form([
            'app_newsletter_subscription[email]' => 'titouan.galopin@en-marche.fr',
            'app_newsletter_subscription[postalCode]' => '10000',
        ]));

        // Subscription should have been saved
        $this->assertCount(1, $this->subscriptionsRepository->findAll());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadHomeBlockData::class,
        ]);

        $this->client = static::createClient();
        $this->container = $this->client->getContainer();
        $this->subscriptionsRepository = $this->getNewsletterSubscriptionRepository();
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);

        $this->client = null;
        $this->subscriptionsRepository = null;
        $this->container = null;

        parent::tearDown();
    }
}
