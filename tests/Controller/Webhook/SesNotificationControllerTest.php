<?php

declare(strict_types=1);

namespace Tests\App\Controller\Webhook;

use App\DataFixtures\ORM\LoadAdherentMessageData;
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

    public function testOpenEngagementEventRecordsAppHit(): void
    {
        $adherent = $this->getAdherentRepository()->findOneByEmail('adherent-male-a@en-marche-dev.fr');
        $campaignUuid = LoadAdherentMessageData::MESSAGE_02_UUID;

        $before = $this->countEmailHits($campaignUuid, $adherent->getId(), 'open');

        $this->postNotification(self::KEY, $this->engagementNotification('Open', $campaignUuid, $adherent->getUuidAsString(), [
            'open' => ['timestamp' => '2024-01-15T10:30:00.000Z'],
        ]));

        self::assertResponseIsSuccessful();
        self::assertSame($before + 1, $this->countEmailHits($campaignUuid, $adherent->getId(), 'open'));
    }

    public function testClickEngagementEventRecordsAppHitWithUrl(): void
    {
        $adherent = $this->getAdherentRepository()->findOneByEmail('adherent-male-a@en-marche-dev.fr');
        $campaignUuid = LoadAdherentMessageData::MESSAGE_02_UUID;

        $before = $this->countEmailHits($campaignUuid, $adherent->getId(), 'click');

        $this->postNotification(self::KEY, $this->engagementNotification('Click', $campaignUuid, $adherent->getUuidAsString(), [
            'click' => ['timestamp' => '2024-01-15T11:00:00.000Z', 'link' => 'https://parti-renaissance.fr/x'],
        ]));

        self::assertResponseIsSuccessful();
        self::assertSame($before + 1, $this->countEmailHits($campaignUuid, $adherent->getId(), 'click'));

        $url = $this->manager->getConnection()->fetchOne(
            'SELECT target_url FROM app_hit WHERE object_id = ? AND adherent_id = ? AND event_type = ? ORDER BY id DESC LIMIT 1',
            [$campaignUuid, $adherent->getId(), 'click']
        );
        self::assertSame('https://parti-renaissance.fr/x', $url);
    }

    public function testRedeliveredOpenIsDeduplicated(): void
    {
        $adherent = $this->getAdherentRepository()->findOneByEmail('adherent-male-a@en-marche-dev.fr');
        $campaignUuid = LoadAdherentMessageData::MESSAGE_02_UUID;
        $body = $this->engagementNotification('Open', $campaignUuid, $adherent->getUuidAsString(), [
            'open' => ['timestamp' => '2024-02-20T09:00:00.000Z'],
        ]);

        $before = $this->countEmailHits($campaignUuid, $adherent->getId(), 'open');
        $this->postNotification(self::KEY, $body);
        $this->postNotification(self::KEY, $body);

        // Same event delivered twice (SNS at-least-once) resolves to one fingerprint, one row.
        self::assertSame($before + 1, $this->countEmailHits($campaignUuid, $adherent->getId(), 'open'));
    }

    public function testEngagementForUnknownMessageRecordsNothing(): void
    {
        $adherent = $this->getAdherentRepository()->findOneByEmail('adherent-male-a@en-marche-dev.fr');
        $unknownCampaign = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';

        $before = $this->countEmailHits($unknownCampaign, $adherent->getId(), 'open');

        $this->postNotification(self::KEY, $this->engagementNotification('Open', $unknownCampaign, $adherent->getUuidAsString(), [
            'open' => ['timestamp' => '2024-03-01T09:00:00.000Z'],
        ]));

        self::assertResponseIsSuccessful();
        self::assertSame($before, $this->countEmailHits($unknownCampaign, $adherent->getId(), 'open'));
    }

    public function testEngagementForUnknownAdherentRecordsNothing(): void
    {
        $campaignUuid = LoadAdherentMessageData::MESSAGE_02_UUID;
        $unknownAdherentUuid = 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb';

        $before = (int) $this->manager->getConnection()->fetchOne('SELECT COUNT(*) FROM app_hit');

        $this->postNotification(self::KEY, $this->engagementNotification('Open', $campaignUuid, $unknownAdherentUuid, [
            'open' => ['timestamp' => '2024-04-01T09:00:00.000Z'],
        ]));

        self::assertResponseIsSuccessful();
        self::assertSame($before, (int) $this->manager->getConnection()->fetchOne('SELECT COUNT(*) FROM app_hit'));
    }

    private function engagementNotification(string $type, string $campaignUuid, string $adherentUuid, array $eventBody): string
    {
        return json_encode([
            'Type' => 'Notification',
            'TopicArn' => self::TOPIC_ARN,
            'MessageId' => 'sns-engagement-1',
            'Message' => json_encode(array_merge([
                'eventType' => $type,
                'mail' => ['tags' => [
                    'campaign_uuid' => [$campaignUuid],
                    'adherent_uuid' => [$adherentUuid],
                ]],
            ], $eventBody)),
        ]);
    }

    private function countEmailHits(string $objectId, int $adherentId, string $eventType): int
    {
        return (int) $this->manager->getConnection()->fetchOne(
            'SELECT COUNT(*) FROM app_hit WHERE object_id = ? AND adherent_id = ? AND event_type = ? AND source = ?',
            [$objectId, $adherentId, $eventType, 'email']
        );
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
