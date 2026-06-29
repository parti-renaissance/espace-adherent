<?php

declare(strict_types=1);

namespace Tests\App\Controller\Webhook;

use App\Mailchimp\Contact\ContactStatusEnum;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractRenaissanceWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
class SesNotificationControllerTest extends AbstractRenaissanceWebTestCase
{
    use ControllerTestTrait;

    private const KEY = 'ses-test-key';
    private const TOPIC_ARN = 'arn:aws:sns:eu-west-3:000000000000:ses-feedback-test';

    public function testPermanentBounceNotificationFlagsRecipient(): void
    {
        $email = 'adherent-male-a@en-marche-dev.fr';
        $adherent = $this->getAdherentRepository()->findOneByEmail($email);

        $this->postNotification(self::KEY, $this->notification('Bounce', [
            'bounce' => ['bounceType' => 'Permanent', 'bouncedRecipients' => [['emailAddress' => $email]]],
        ]));

        self::assertResponseIsSuccessful();
        self::assertTrue($adherent->isEmailHardBounced());
    }

    public function testComplaintNotificationUnsubscribesRecipient(): void
    {
        $email = 'adherent-female-f@en-marche-dev.fr';
        $adherent = $this->getAdherentRepository()->findOneByEmail($email);

        $this->postNotification(self::KEY, $this->notification('Complaint', [
            'complaint' => ['complainedRecipients' => [['emailAddress' => $email]]],
        ]));

        self::assertResponseIsSuccessful();
        self::assertSame(ContactStatusEnum::UNSUBSCRIBED, $adherent->getMailchimpStatus());
        self::assertNotNull($adherent->unsubscribeRequestedAt);
    }

    public function testInvalidKeyIsForbidden(): void
    {
        $this->postNotification('wrong-key', $this->notification('Bounce', [
            'bounce' => ['bounceType' => 'Permanent', 'bouncedRecipients' => [['emailAddress' => 'x@example.org']]],
        ]));

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testUnexpectedTopicArnIsForbidden(): void
    {
        $body = json_encode([
            'Type' => 'Notification',
            'TopicArn' => 'arn:aws:sns:eu-west-3:000000000000:someone-elses-topic',
            'MessageId' => 'sns-x',
            'Message' => json_encode(['eventType' => 'Complaint', 'complaint' => ['complainedRecipients' => []]]),
        ]);

        $this->postRaw(self::KEY, $body);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testSubscriptionConfirmationIsAcceptedWithoutSideEffect(): void
    {
        $body = json_encode([
            'Type' => 'SubscriptionConfirmation',
            'TopicArn' => self::TOPIC_ARN,
            'SubscribeURL' => 'https://sns.eu-west-3.amazonaws.com/?Action=ConfirmSubscription&Token=abc',
        ]);

        $this->postRaw(self::KEY, $body);

        self::assertResponseIsSuccessful();
    }

    private function notification(string $type, array $eventBody): string
    {
        return json_encode([
            'Type' => 'Notification',
            'TopicArn' => self::TOPIC_ARN,
            'MessageId' => 'sns-1',
            'Message' => json_encode(array_merge(['eventType' => $type], $eventBody)),
        ]);
    }

    private function postNotification(string $key, string $body): void
    {
        $this->postRaw($key, $body);
    }

    private function postRaw(string $key, string $body): void
    {
        $this->client->request(
            Request::METHOD_POST,
            '/ses/notification/'.$key,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $body
        );
    }
}
