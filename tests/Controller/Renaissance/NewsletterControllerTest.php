<?php

namespace Tests\App\Controller\Renaissance;

use App\Entity\Renaissance\NewsletterSubscription;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class NewsletterControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testRenaissanceMembershipRequest(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/');

        $this->client->submit($crawler->filter('form[name="newsletter_subscription"]')->form([
            'frc-captcha-solution' => 'fake',
            'newsletter_subscription' => [
                'firstName' => 'Jules',
            ],
        ]));

        $crawler = $this->client->followRedirect();

        self::assertStringContainsString('Cette valeur ne doit pas être vide.', $crawler->filter('#newsletter-form-error')->text());

        $this->client->submit($crawler->filter('form[name="newsletter_subscription"]')->form([
            'frc-captcha-solution' => 'fake',
            'newsletter_subscription' => [
                'firstName' => 'Jules',
                'email' => 'jules@en-marche-dev.fr',
                'zipCode' => '06500',
                'cguAccepted' => true,
                'conditions' => true,
            ],
        ]));

        $crawler = $this->client->followRedirect();

        self::assertStringContainsString('Merci pour votre inscription !', $crawler->filter('div[role="alert"]')->text());

        $entities = $this->getEntityManager()->getRepository(NewsletterSubscription::class)->findAll();
        self::assertCount(1, $entities);

        /** @var NewsletterSubscription $nl */
        $nl = current($entities);

        self::assertSame('Jules', $nl->firstName);
        self::assertSame('jules@en-marche-dev.fr', $nl->email);
        self::assertSame('06500', $nl->zipCode);
        self::assertNotEmpty($nl->token);
        self::assertEmpty($nl->confirmedAt);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->makeRenaissanceClient();
    }
}
