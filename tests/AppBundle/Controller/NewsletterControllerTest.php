<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\NewsletterSubscription;
use Goutte\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NewsletterControllerTest extends WebTestCase
{
    public function testFullProcess()
    {
        $symfonyClient = static::createClient();
        $externalClient = new Client();

        $entityManager = $symfonyClient->getContainer()->get('doctrine.orm.entity_manager');
        $newsletterRepository = $entityManager->getRepository('AppBundle:NewsletterSubscription');

        // There should not be any newsletterSubscription for the moment
        $this->assertEmpty($newsletterRepository->findAll());

        /*
         * Initial questions page
         */
        $crawler = $symfonyClient->request('GET', '/newsletter');
        $this->assertEquals(200, $symfonyClient->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=app_newsletter]')->form([
            'app_newsletter[email]' => 'test@newsletter.com',
            'app_newsletter[postalCode]' => '10000',
        ]);

        $symfonyClient->submit($form);

        // NewsletterSubscription should have been saved
        $newsletterSubscriptions = $newsletterRepository->findAll();
        $this->assertCount(1, $newsletterSubscriptions);

        /** @var NewsletterSubscription $newsletterSubscription */
        $newsletterSubscription = $newsletterSubscriptions[0];

        $this->assertEquals('test@newsletter.com', $newsletterSubscription->getEmail());
        $this->assertEquals('10000', $newsletterSubscription->getPostalCode());

        // We should be redirected to thanks page
        $this->assertEquals(302, $symfonyClient->getResponse()->getStatusCode());

        $crawler = $symfonyClient->followRedirect();
        $this->assertEquals(200, $symfonyClient->getResponse()->getStatusCode());
    }
}
