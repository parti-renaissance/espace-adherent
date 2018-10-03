<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadNewsletterSubscriptionData;
use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Mail\Transactional\NewsletterInvitationMail;
use AppBundle\Mail\Transactional\NewsletterSubscriptionMail;
use AppBundle\Repository\EmailRepository;
use AppBundle\Repository\NewsletterInviteRepository;
use AppBundle\Repository\NewsletterSubscriptionRepository;
use AppBundle\DataFixtures\ORM\LoadHomeBlockData;
use EnMarche\MailerBundle\Test\MailTestCaseTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * @group functional
 * @group controller
 */
class NewsletterControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use MailTestCaseTrait;

    /** @var NewsletterSubscriptionRepository */
    private $subscriptionsRepository;

    /** @var NewsletterInviteRepository */
    private $newsletterInviteRepository;

    /** @var EmailRepository */
    private $emailRepository;

    public function testSubscriptionAndRetry()
    {
        $this->assertCount(5, $this->subscriptionsRepository->findAll());

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, '/newsletter');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->submit($crawler->filter('form[name=app_newsletter_subscription]')->form([
            'app_newsletter_subscription[email]' => 'titouan.galopin@en-marche.fr',
            'app_newsletter_subscription[postalCode]' => '10000',
        ]));

        $this->isSuccessful($this->client->getResponse());
        $this->assertCount(1, $errors = $crawler->filter('.form__errors'));
        $this->assertSame('L\'acceptation des mentions d\'information est obligatoire pour donner suite à votre demande.', $errors->eq(0)->text());

        $this->client->submit($crawler->filter('form[name=app_newsletter_subscription]')->form([
            'app_newsletter_subscription[email]' => 'titouan.galopin@en-marche.fr',
            'app_newsletter_subscription[postalCode]' => '10000',
            'app_newsletter_subscription[personalDataCollection]' => true,
        ]));

        // Subscription should have been saved
        $this->assertCount(6, $subscriptions = $this->subscriptionsRepository->findAll());

        /** @var NewsletterSubscription $subscription */
        $subscription = $subscriptions[5];

        $this->assertSame('titouan.galopin@en-marche.fr', $subscription->getEmail());
        $this->assertSame('10000', $subscription->getPostalCode());
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());

        // Email should have been sent
        $this->assertMailCountForClass(1, NewsletterSubscriptionMail::class);

        // Try another time with the same email (should fail)
        $crawler = $this->client->request(Request::METHOD_GET, '/newsletter');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name=app_newsletter_subscription]')->form([
            'app_newsletter_subscription[email]' => 'titouan.galopin@en-marche.fr',
            'app_newsletter_subscription[postalCode]' => '20000',
            'app_newsletter_subscription[personalDataCollection]' => true,
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Subscription should not have been saved
        $this->assertCount(6, $this->subscriptionsRepository->findAll());
    }

    public function testSubscriptionFromHome()
    {
        $this->assertCount(5, $this->subscriptionsRepository->findAll());

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, '/');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name=app_newsletter_subscription]')->form([
            'app_newsletter_subscription[email]' => 'titouan.galopin@en-marche.fr',
            'app_newsletter_subscription[postalCode]' => '10000',
            'app_newsletter_subscription[personalDataCollection]' => true,
        ]));

        // Subscription should have been saved
        $this->assertCount(6, $this->subscriptionsRepository->findAll());

        // Email should have been sent
        $this->assertMailCountForClass(1, NewsletterSubscriptionMail::class);
    }

    public function testInvitationAndRetry()
    {
        $this->assertCount(0, $this->newsletterInviteRepository->findAll());

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, '/newsletter/invitation');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name=newsletter_invitation]')->form([
            'newsletter_invitation[firstName]' => 'Titouan',
            'newsletter_invitation[lastName]' => 'Galopin',
            'newsletter_invitation[guests][0]' => 'hugo.hamon@clichy-beach.com',
            'newsletter_invitation[guests][1]' => 'jules.pietri@clichy-beach.com',
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/newsletter/invitation/merci', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertContains('Vos 2 invitations ont bien été envoyées', trim($crawler->filter('.newsletter-result > h2')->text()));

        // Invitations should have been saved
        $this->assertCount(2, $invitations = $this->newsletterInviteRepository->findAll());

        $invite1 = $this->newsletterInviteRepository->findMostRecentInvite('hugo.hamon@clichy-beach.com');
        $this->assertSame('hugo.hamon@clichy-beach.com', $invite1->getEmail());
        $this->assertSame('Titouan Galopin', $invite1->getSenderFullName());

        $invite2 = $this->newsletterInviteRepository->findMostRecentInvite('jules.pietri@clichy-beach.com');
        $this->assertSame('jules.pietri@clichy-beach.com', $invite2->getEmail());
        $this->assertSame('Titouan Galopin', $invite2->getSenderFullName());

        // Email should have been sent
        $this->assertMailSentForRecipientContainsVars(
            $invite1->getEmail(),
            [
                'sender_firstname' => 'Titouan',
                'subscribe_link' => 'http://test.enmarche.code/newsletter?mail=hugo.hamon%40clichy-beach.com',
            ],
            NewsletterInvitationMail::class
        );
        $this->assertMailSentForRecipientContainsVars(
            $invite2->getEmail(),
            [
                'sender_firstname' => 'Titouan',
                'subscribe_link' => 'http://test.enmarche.code/newsletter?mail=jules.pietri%40clichy-beach.com',
            ],
            NewsletterInvitationMail::class
        );
    }

    public function testInvitationSentWithoutRedirection()
    {
        $this->client->request(Request::METHOD_GET, '/newsletter/invitation/merci');

        $this->assertResponseStatusCode(Response::HTTP_PRECONDITION_FAILED, $this->client->getResponse());
    }

    public function testUnsubscribeAndResubscribe()
    {
        $this->assertCount(5, $this->subscriptionsRepository->findAll());

        $subscription = $this->subscriptionsRepository->findOneBy(['email' => 'abc@en-marche-dev.fr']);

        $this->assertInstanceOf(NewsletterSubscription::class, $subscription);
        $this->assertSame('92110', $subscription->getPostalCode());

        // Initial unsubscribe form
        $crawler = $this->client->request(Request::METHOD_GET, '/newsletter/desinscription');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name=app_newsletter_unsubscribe]')->form([
            'app_newsletter_unsubscribe[email]' => 'abc@en-marche-dev.fr',
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/newsletter/desinscription/desinscrit', $this->client);

        $this->assertCount(4, $this->subscriptionsRepository->findAll());
        $this->assertNull($this->subscriptionsRepository->findOneBy(['email' => 'abc@en-marche-dev.fr']));

        // Initial subscribe form
        $crawler = $this->client->request(Request::METHOD_GET, '/newsletter');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name=app_newsletter_subscription]')->form([
            'app_newsletter_subscription[email]' => 'abc@en-marche-dev.fr',
            'app_newsletter_subscription[postalCode]' => '59000',
            'app_newsletter_subscription[personalDataCollection]' => true,
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());

        $this->manager->clear();

        $this->assertCount(5, $subscriptions = $this->subscriptionsRepository->findAll());

        $subscription = $this->subscriptionsRepository->findOneBy(['email' => 'abc@en-marche-dev.fr']);

        $this->assertInstanceOf(NewsletterSubscription::class, $subscription);
        $this->assertSame('59000', $subscription->getPostalCode());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadHomeBlockData::class,
            LoadNewsletterSubscriptionData::class,
        ]);

        $this->clearMails();
        $this->subscriptionsRepository = $this->getNewsletterSubscriptionRepository();
        $this->newsletterInviteRepository = $this->getNewsletterInvitationRepository();
        $this->emailRepository = $this->getEmailRepository();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->subscriptionsRepository = null;
        $this->newsletterInviteRepository = null;
        $this->emailRepository = null;

        $this->clearMails();

        parent::tearDown();
    }
}
