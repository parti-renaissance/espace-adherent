<?php

namespace Tests\App\Controller\Renaissance;

use App\Entity\Renaissance\NewsletterSubscription;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractRenaissanceWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
class NewsletterControllerTest extends AbstractRenaissanceWebTestCase
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
}
