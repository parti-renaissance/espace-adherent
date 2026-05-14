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
        $this->postSubscription([
            'email' => $email = 'jules@en-marche-dev.fr',
            'postal_code' => '06500',
            'recaptcha' => 'fake',
            'cgu_accepted' => true,
            'source' => 'site_eu',
        ]);

        $this->assertResponseStatusCodeSame(201);

        $subscription = $this->findSubscriptionByEmail($email);
        self::assertNotNull($subscription);
        self::assertSame('06500', $subscription->zipCode);
        self::assertSame('site_eu', $subscription->source);
        self::assertNotEmpty($subscription->token);
        self::assertEmpty($subscription->confirmedAt);

        $this->assertCountMails(1, RenaissanceNewsletterSubscriptionConfirmationMessage::class, $email);
    }

    public function testNewsletterRequestIsRejectedForUnknownSource(): void
    {
        $this->postSubscription([
            'email' => 'unknown-source@en-marche-dev.fr',
            'postal_code' => '75001',
            'recaptcha' => 'fake',
            'cgu_accepted' => true,
            'source' => 'source_qui_nexiste_pas',
        ]);

        $this->assertResponseStatusCodeSame(400);

        $body = $this->client->getResponse()->getContent();
        self::assertStringContainsString('source', $body);
        self::assertNull($this->findSubscriptionByEmail('unknown-source@en-marche-dev.fr'));
    }

    public function testNewsletterRequestIsRejectedForDisabledSource(): void
    {
        $this->postSubscription([
            'email' => 'disabled-source@en-marche-dev.fr',
            'postal_code' => '75001',
            'recaptcha' => 'fake',
            'cgu_accepted' => true,
            'source' => 'campagne_test_desactivee',
        ]);

        $this->assertResponseStatusCodeSame(400);

        $body = $this->client->getResponse()->getContent();
        self::assertStringContainsString('source', $body);
        self::assertNull($this->findSubscriptionByEmail('disabled-source@en-marche-dev.fr'));
    }

    public function testConfirmationRedirectsToCustomUrlWhenSourceDefinesOne(): void
    {
        $email = 'confirm-custom@en-marche-dev.fr';

        $this->postSubscription([
            'email' => $email,
            'postal_code' => '75008',
            'recaptcha' => 'fake',
            'cgu_accepted' => true,
            'source' => 'site_eu',
        ]);
        $this->assertResponseStatusCodeSame(201);

        $subscription = $this->findSubscriptionByEmail($email);
        self::assertNotNull($subscription);

        $this->requestConfirmation($subscription);

        $this->assertResponseRedirects('https://legislatives.parti-renaissance.dev/confirmation-newsletter');

        $confirmed = $this->findSubscriptionByEmail($email);
        self::assertNotNull($confirmed);
        self::assertNotNull($confirmed->confirmedAt);
    }

    public function testConfirmationFallsBackToDefaultRedirectWhenSourceHasNoUrl(): void
    {
        $email = 'confirm-default@en-marche-dev.fr';

        $this->postSubscription([
            'email' => $email,
            'postal_code' => '35000',
            'recaptcha' => 'fake',
            'cgu_accepted' => true,
            'source' => 'site_renaissance',
        ]);
        $this->assertResponseStatusCodeSame(201);

        $subscription = $this->findSubscriptionByEmail($email);
        self::assertNotNull($subscription);

        $this->requestConfirmation($subscription);

        self::assertResponseStatusCodeSame(302);
        $location = $this->client->getResponse()->headers->get('Location');
        self::assertNotNull($location);
        self::assertStringContainsString('/app', $location);
    }

    private function postSubscription(array $payload): void
    {
        $this->client->request(
            Request::METHOD_POST,
            '/api/newsletter',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );
    }

    private function requestConfirmation(NewsletterSubscription $subscription): void
    {
        $this->client->request(
            Request::METHOD_GET,
            \sprintf(
                '/newsletter/confirmation/%s/%s',
                $subscription->getUuid()->toRfc4122(),
                $subscription->token->toRfc4122()
            )
        );
    }

    private function findSubscriptionByEmail(string $email): ?NewsletterSubscription
    {
        return $this->getEntityManager()
            ->getRepository(NewsletterSubscription::class)
            ->findOneBy(['email' => $email]);
    }
}
