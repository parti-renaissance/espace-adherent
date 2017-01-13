<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\NewsletterSubscription;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NewsletterControllerTest extends WebTestCase
{
    public function testSubscriptionAndRetry()
    {
        $client = static::createClient();

        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $subscriptionsRepository = $entityManager->getRepository('AppBundle:NewsletterSubscription');

        // There should not be any subscription for the moment
        $this->assertEmpty($subscriptionsRepository->findAll());

        // Initial form
        $crawler = $client->request('GET', '/newsletter');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=app_newsletter_subscription]')->form([
            'app_newsletter_subscription[email]' => 'titouan.galopin@en-marche.fr',
            'app_newsletter_subscription[postalCode]' => '10000',
        ]);

        $client->submit($form);

        // Subscription should have been saved
        $subscriptions = $subscriptionsRepository->findAll();
        $this->assertCount(1, $subscriptions);

        /** @var NewsletterSubscription $subscription */
        $subscription = $subscriptions[0];

        $this->assertEquals('titouan.galopin@en-marche.fr', $subscription->getEmail());
        $this->assertEquals('10000', $subscription->getPostalCode());

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Try another time with the same email (should fail)
        $crawler = $client->request('GET', '/newsletter');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=app_newsletter_subscription]')->form([
            'app_newsletter_subscription[email]' => 'titouan.galopin@en-marche.fr',
            'app_newsletter_subscription[postalCode]' => '20000',
        ]);

        $client->submit($form);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Subscription should not have been saved
        $subscriptions = $subscriptionsRepository->findAll();
        $this->assertCount(1, $subscriptions);

        $entityManager->remove($subscriptions[0]);
        $entityManager->flush();
    }

    public function testSubscriptionFromHome()
    {
        $client = static::createClient();

        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $subscriptionsRepository = $entityManager->getRepository('AppBundle:NewsletterSubscription');

        // There should not be any donation for the moment
        $this->assertEmpty($subscriptionsRepository->findAll());

        // Initial form
        $crawler = $client->request('GET', '/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=app_newsletter_subscription]')->form([
            'app_newsletter_subscription[email]' => 'titouan.galopin@en-marche.fr',
            'app_newsletter_subscription[postalCode]' => '10000',
        ]);

        $client->submit($form);

        // Subscription should have been saved
        $subscriptions = $subscriptionsRepository->findAll();
        $this->assertCount(1, $subscriptions);

        $entityManager->remove($subscriptions[0]);
        $entityManager->flush();
    }
}
