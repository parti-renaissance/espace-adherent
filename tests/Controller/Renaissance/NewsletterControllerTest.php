<?php

declare(strict_types=1);

namespace Tests\App\Controller\Renaissance;

use App\Entity\Renaissance\NewsletterSubscription;
use App\Mailer\Message\Renaissance\RenaissanceNewsletterSubscriptionConfirmationMessage;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractRenaissanceWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
class NewsletterControllerTest extends AbstractRenaissanceWebTestCase
{
    use ControllerTestTrait;

    public function testNewsletterRequest(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            '/api/newsletter',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode([
                'email' => $email = 'jules@en-marche-dev.fr',
                'postal_code' => '06500',
                'recaptcha' => 'fake',
                'cgu_accepted' => true,
                'source' => 'site_eu',
            ])
        );

        $this->assertResponseStatusCodeSame(201);

        $entities = $this->getEntityManager()->getRepository(NewsletterSubscription::class)->findAll();
        self::assertCount(1, $entities);

        /** @var NewsletterSubscription $nl */
        $nl = current($entities);

        self::assertSame($email, $nl->email);
        self::assertSame('06500', $nl->zipCode);
        self::assertNotEmpty($nl->token);
        self::assertEmpty($nl->confirmedAt);

        $this->assertCountMails(1, RenaissanceNewsletterSubscriptionConfirmationMessage::class, $email);
    }
}
