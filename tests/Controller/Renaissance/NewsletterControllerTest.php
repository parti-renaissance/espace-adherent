<?php

declare(strict_types=1);

namespace Tests\App\Controller\Renaissance;

use App\DataFixtures\ORM\LoadTransactionalEmailTemplateData;
use App\Entity\Email\EmailLog;
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

    public function testGetDoesNotConfirmSubscriptionToProtectAgainstLinkScanners(): void
    {
        $email = 'confirm-get-only@en-marche-dev.fr';

        $this->postSubscription([
            'email' => $email,
            'postal_code' => '75001',
            'recaptcha' => 'fake',
            'cgu_accepted' => true,
            'source' => 'site_renaissance',
        ]);
        $this->assertResponseStatusCodeSame(201);

        $subscription = $this->findSubscriptionByEmail($email);
        self::assertNotNull($subscription);

        // A GET (e.g. an email security scanner pre-fetching the link) only renders the
        // interstitial page and must NOT confirm the subscription.
        $this->requestConfirmation($subscription, Request::METHOD_GET);
        $this->assertResponseIsSuccessful();
        self::assertNull($this->findSubscriptionByEmail($email)->confirmedAt);

        // The interstitial auto-submits a POST, which performs the actual confirmation.
        $this->requestConfirmation($subscription, Request::METHOD_POST);
        self::assertNotNull($this->findSubscriptionByEmail($email)->confirmedAt);
    }

    public function testConfirmationEmailUsesSourceConfiguredTemplate(): void
    {
        $this->postSubscription([
            'email' => $email = 'confirm-template@en-marche-dev.fr',
            'postal_code' => '69001',
            'recaptcha' => 'fake',
            'cgu_accepted' => true,
            'source' => 'site_ensemble',
        ]);
        $this->assertResponseStatusCodeSame(201);

        $message = $this->getConfirmationEmail($email);
        $payload = json_decode($message->getRequestPayloadJson(), true);

        // Empty template_name means a TransactionalEmailTemplate object was resolved
        // (the per-source override), not the default class-name lookup path.
        self::assertSame('', $payload['template_name']);
        // ... and the resolved template is the one configured on the source.
        self::assertSame(LoadTransactionalEmailTemplateData::NEWSLETTER_CONFIRMATION_SUBJECT, $payload['message']['subject']);
        // The log records the template's EmailSender, not the message-level "Renaissance" default.
        self::assertSame('Ne pas répondre', $message->getSender());
    }

    public function testConfirmationEmailFallsBackToDefaultTemplateWhenSourceHasNoTemplate(): void
    {
        $this->postSubscription([
            'email' => $email = 'confirm-default-template@en-marche-dev.fr',
            'postal_code' => '35000',
            'recaptcha' => 'fake',
            'cgu_accepted' => true,
            'source' => 'site_renaissance',
        ]);
        $this->assertResponseStatusCodeSame(201);

        $payload = json_decode($this->getConfirmationEmail($email)->getRequestPayloadJson(), true);

        // No template configured on the source: fallback to the message class-name template.
        self::assertSame('renaissance-newsletter-subscription-confirmation', $payload['template_name']);
        self::assertSame('Confirmez votre adresse email', $payload['message']['subject']);
    }

    public function testUnconfirmedResubscriptionUpdatesSourceAndResendsConfirmation(): void
    {
        $email = 'resub-unconfirmed@en-marche-dev.fr';

        // First subscription on a source WITHOUT a configured template.
        $this->postSubscription([
            'email' => $email,
            'postal_code' => '75001',
            'recaptcha' => 'fake',
            'cgu_accepted' => true,
            'source' => 'site_renaissance',
        ]);
        $this->assertResponseStatusCodeSame(201);

        // Re-subscribe while still unconfirmed, on a source WITH a configured template.
        $this->postSubscription([
            'email' => $email,
            'postal_code' => '75001',
            'recaptcha' => 'fake',
            'cgu_accepted' => true,
            'source' => 'site_ensemble',
        ]);
        $this->assertResponseStatusCodeSame(201);

        // The existing (single) subscription has been updated to the new source, still unconfirmed.
        $subscription = $this->findSubscriptionByEmail($email);
        self::assertNotNull($subscription);
        self::assertSame('site_ensemble', $subscription->source);
        self::assertNull($subscription->confirmedAt);

        // Two confirmation emails were sent: the first with the fallback template, the second with
        // the new source's template (template_name empty = a TransactionalEmailTemplate was resolved).
        $messages = $this->getEmailRepository()->findRecipientMessages(
            RenaissanceNewsletterSubscriptionConfirmationMessage::class,
            $email
        );
        self::assertCount(2, $messages);

        $templateNames = array_map(
            static fn ($message) => json_decode($message->getRequestPayloadJson(), true)['template_name'],
            $messages
        );
        sort($templateNames);
        self::assertSame(['', 'renaissance-newsletter-subscription-confirmation'], $templateNames);
    }

    public function testConfirmedResubscriptionIsRejected(): void
    {
        $email = 'resub-confirmed@en-marche-dev.fr';

        $this->postSubscription([
            'email' => $email,
            'postal_code' => '75001',
            'recaptcha' => 'fake',
            'cgu_accepted' => true,
            'source' => 'site_renaissance',
        ]);
        $this->assertResponseStatusCodeSame(201);

        // Confirm the subscription.
        $subscription = $this->findSubscriptionByEmail($email);
        self::assertNotNull($subscription);
        $this->requestConfirmation($subscription);
        self::assertNotNull($this->findSubscriptionByEmail($email)->confirmedAt);

        // Re-subscribing a confirmed email is rejected.
        $this->postSubscription([
            'email' => $email,
            'postal_code' => '75001',
            'recaptcha' => 'fake',
            'cgu_accepted' => true,
            'source' => 'site_renaissance',
        ]);
        $this->assertResponseStatusCodeSame(400);
        $body = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertStringContainsString('déjà inscrit', $body['violations'][0]['message']);
    }

    private function getConfirmationEmail(string $email): EmailLog
    {
        $messages = $this->getEmailRepository()->findRecipientMessages(
            RenaissanceNewsletterSubscriptionConfirmationMessage::class,
            $email
        );
        self::assertCount(1, $messages);

        return $messages[0];
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

    private function requestConfirmation(NewsletterSubscription $subscription, string $method = Request::METHOD_POST): void
    {
        $this->client->request(
            $method,
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
