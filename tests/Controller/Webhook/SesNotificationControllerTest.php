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

    public function testDirectNotificationFormatSuppressesRecipient(): void
    {
        // Legacy direct identity notification: "notificationType" instead of "eventType", no campaign tags.
        // Must still trigger the email-keyed suppression (regression guard for the type normalisation).
        $email = 'adherent-male-a@en-marche-dev.fr';
        $adherent = $this->getAdherentRepository()->findOneByEmail($email);

        $this->postNotification(self::KEY, json_encode([
            'Type' => 'Notification',
            'TopicArn' => self::TOPIC_ARN,
            'MessageId' => 'sns-direct-bounce',
            'Message' => json_encode([
                'notificationType' => 'Bounce',
                'bounce' => ['bounceType' => 'Permanent', 'bouncedRecipients' => [['emailAddress' => $email]]],
            ]),
        ]));

        self::assertResponseIsSuccessful();
        self::assertTrue($adherent->isEmailHardBounced());
    }

    public function testComplaintProcessingIsIdempotentEndToEnd(): void
    {
        $email = 'adherent-female-f@en-marche-dev.fr';
        $adherent = $this->getAdherentRepository()->findOneByEmail($email);

        $complaint = static fn (string $messageId): string => json_encode([
            'Type' => 'Notification',
            'TopicArn' => self::TOPIC_ARN,
            'MessageId' => $messageId,
            'Message' => json_encode([
                'eventType' => 'Complaint',
                'complaint' => ['complainedRecipients' => [['emailAddress' => $email]]],
            ]),
        ]);

        $this->postNotification(self::KEY, $complaint('sns-complaint-a'));
        $complainedAt = $adherent->emailComplainedAt;
        $historiesAfterFirst = $this->countSubscriptionHistories();

        // Same complaint, distinct SNS MessageId => a second genuine business processing (not raw dedup).
        $this->postNotification(self::KEY, $complaint('sns-complaint-b'));

        self::assertNotNull($complainedAt);
        self::assertSame($complainedAt, $adherent->emailComplainedAt, 'complaint timestamp must not be overwritten on reprocessing');
        self::assertSame($historiesAfterFirst, $this->countSubscriptionHistories(), 'reprocessing must not create a new subscription history');
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

    public function testNotificationIsCapturedInRawStore(): void
    {
        $body = json_encode([
            'Type' => 'Notification',
            'TopicArn' => self::TOPIC_ARN,
            'MessageId' => 'sns-raw-1',
            'Message' => json_encode([
                'eventType' => 'Delivery',
                'mail' => [
                    'messageId' => 'ses-msg-raw',
                    'destination' => ['raw-recipient@example.org'],
                    'tags' => ['campaign_uuid' => [LoadAdherentMessageData::MESSAGE_02_UUID]],
                ],
                'delivery' => ['timestamp' => '2024-05-01T08:00:00.000Z'],
            ]),
        ]);

        $this->postNotification(self::KEY, $body);

        self::assertResponseIsSuccessful();

        $row = $this->manager->getConnection()->fetchAssociative('SELECT * FROM ses_event WHERE sns_message_id = ?', ['sns-raw-1']);
        self::assertIsArray($row);
        self::assertSame('Delivery', $row['event_type']);
        self::assertSame('ses-msg-raw', $row['ses_message_id']);
        self::assertSame(LoadAdherentMessageData::MESSAGE_02_UUID, $row['campaign_uuid']);
        self::assertSame('raw-recipient@example.org', $row['recipient']);
        self::assertNotNull($row['payload']);
    }

    public function testRawCaptureIsIdempotentOnSnsMessageId(): void
    {
        $body = json_encode([
            'Type' => 'Notification',
            'TopicArn' => self::TOPIC_ARN,
            'MessageId' => 'sns-raw-idem',
            'Message' => json_encode(['eventType' => 'Send', 'mail' => ['timestamp' => '2024-05-01T08:00:00.000Z']]),
        ]);

        $this->postNotification(self::KEY, $body);
        $this->postNotification(self::KEY, $body);

        // SNS at-least-once: the same MessageId resolves to a single row via the UPSERT.
        self::assertSame(1, (int) $this->manager->getConnection()->fetchOne('SELECT COUNT(*) FROM ses_event WHERE sns_message_id = ?', ['sns-raw-idem']));
    }

    public function testForbiddenNotificationStoresNothingInRawStore(): void
    {
        $before = (int) $this->manager->getConnection()->fetchOne('SELECT COUNT(*) FROM ses_event');

        $this->postNotification('wrong-key', json_encode([
            'Type' => 'Notification',
            'TopicArn' => self::TOPIC_ARN,
            'MessageId' => 'sns-raw-forbidden',
            'Message' => json_encode(['eventType' => 'Delivery']),
        ]));

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        self::assertSame($before, (int) $this->manager->getConnection()->fetchOne('SELECT COUNT(*) FROM ses_event'));
    }

    public function testBounceRunsBothBusinessAndRawCapture(): void
    {
        $email = 'adherent-male-a@en-marche-dev.fr';
        $adherent = $this->getAdherentRepository()->findOneByEmail($email);

        $this->postNotification(self::KEY, json_encode([
            'Type' => 'Notification',
            'TopicArn' => self::TOPIC_ARN,
            'MessageId' => 'sns-raw-bounce',
            'Message' => json_encode([
                'eventType' => 'Bounce',
                'bounce' => ['bounceType' => 'Permanent', 'bouncedRecipients' => [['emailAddress' => $email]]],
            ]),
        ]));

        self::assertResponseIsSuccessful();
        // Business path ran (suppression)…
        self::assertTrue($adherent->isEmailHardBounced());
        // …and the audit path captured the same event, with the bounce-specific recipient.
        $row = $this->manager->getConnection()->fetchAssociative('SELECT event_type, recipient FROM ses_event WHERE sns_message_id = ?', ['sns-raw-bounce']);
        self::assertIsArray($row);
        self::assertSame('Bounce', $row['event_type']);
        self::assertSame($email, $row['recipient']);
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

    private function countSubscriptionHistories(): int
    {
        return (int) $this->manager->getConnection()->fetchOne('SELECT COUNT(*) FROM adherent_email_subscription_histories');
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
