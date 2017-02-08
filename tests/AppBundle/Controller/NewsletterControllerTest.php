<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadNewsletterSubscriptionData;
use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Repository\NewsletterSubscriptionRepository;
use Symfony\Bundle\FrameworkBundle\Client;
use AppBundle\DataFixtures\ORM\LoadHomeBlockData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\SqliteWebTestCase;

class NewsletterControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    /** @var Client */
    private $client;

    /** @var NewsletterSubscriptionRepository */
    private $subscriptionsRepository;

    /**
     * @group functionnal
     */
    public function testSubscriptionAndRetry()
    {
        $this->assertCount(5, $this->subscriptionsRepository->findAll());

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, '/newsletter');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name=app_newsletter_subscription]')->form([
            'app_newsletter_subscription[email]' => 'titouan.galopin@en-marche.fr',
            'app_newsletter_subscription[postalCode]' => '10000',
        ]));

        // Subscription should have been saved
        $this->assertCount(6, $subscriptions = $this->subscriptionsRepository->findAll());

        /** @var NewsletterSubscription $subscription */
        $subscription = $subscriptions[5];

        $this->assertSame('titouan.galopin@en-marche.fr', $subscription->getEmail());
        $this->assertSame('10000', $subscription->getPostalCode());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Try another time with the same email (should fail)
        $crawler = $this->client->request(Request::METHOD_GET, '/newsletter');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name=app_newsletter_subscription]')->form([
            'app_newsletter_subscription[email]' => 'titouan.galopin@en-marche.fr',
            'app_newsletter_subscription[postalCode]' => '20000',
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Subscription should not have been saved
        $this->assertCount(6, $this->subscriptionsRepository->findAll());
    }

    /**
     * @group functionnal
     */
    public function testSubscriptionFromHome()
    {
        $this->assertCount(5, $this->subscriptionsRepository->findAll());

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, '/');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name=app_newsletter_subscription]')->form([
            'app_newsletter_subscription[email]' => 'titouan.galopin@en-marche.fr',
            'app_newsletter_subscription[postalCode]' => '10000',
        ]));

        // Subscription should have been saved
        $this->assertCount(6, $this->subscriptionsRepository->findAll());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadHomeBlockData::class,
            LoadNewsletterSubscriptionData::class,
        ]);

        $this->subscriptionsRepository = $this->getNewsletterSubscriptionRepository();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->subscriptionsRepository = null;

        parent::tearDown();
    }
}
