<?php

declare(strict_types=1);

namespace Tests\App\Unit\Ses\Webhook;

use App\Ses\Webhook\SesPayloadReader;
use App\Ses\Webhook\SesRawEventData;
use App\Ses\Webhook\SesRawEventExtractor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Payloads mirror the AWS SES event-publishing format delivered on SNS: an envelope carrying MessageId and a
 * JSON-encoded Message (eventType + mail{tags, destination, messageId} + a per-type section).
 */
final class SesRawEventExtractorTest extends TestCase
{
    private const SNS_MESSAGE_ID = 'sns-0000-1111-2222-3333';
    private const SES_MESSAGE_ID = 'ses-message-id-abc';
    private const CAMPAIGN_UUID = '11111111-1111-4111-8111-111111111111';
    private const ADHERENT_UUID = '22222222-2222-4222-8222-222222222222';

    public function testExtractsAllColumnsFromDeliveryEvent(): void
    {
        $data = $this->extract($this->snsPayload([
            'eventType' => 'Delivery',
            'mail' => [
                'messageId' => self::SES_MESSAGE_ID,
                'destination' => ['recipient@example.org'],
                'tags' => $this->tags(),
            ],
            'delivery' => ['timestamp' => '2024-01-15T10:30:00.000Z'],
        ]));

        self::assertSame(self::SNS_MESSAGE_ID, $data->snsMessageId);
        self::assertSame('Delivery', $data->eventType);
        self::assertSame(self::SES_MESSAGE_ID, $data->sesMessageId);
        self::assertSame(self::CAMPAIGN_UUID, $data->campaignUuid);
        self::assertSame(self::ADHERENT_UUID, $data->adherentUuid);
        self::assertSame('recipient@example.org', $data->recipient);
        self::assertInstanceOf(\DateTimeImmutable::class, $data->occurredAt);
        self::assertSame('2024-01-15 10:30:00', $data->occurredAt->format('Y-m-d H:i:s'));
        self::assertSame('UTC', $data->occurredAt->getTimezone()->getName());
    }

    /**
     * @param array<string, mixed> $event
     */
    #[DataProvider('provideRecipientPerType')]
    public function testRecipientPrefersEventSpecificAddress(array $event, ?string $expected): void
    {
        self::assertSame($expected, $this->extract($this->snsPayload($event))->recipient);
    }

    public static function provideRecipientPerType(): iterable
    {
        $mail = ['destination' => ['destination@example.org']];

        yield 'delivery uses mail.destination' => [
            ['eventType' => 'Delivery', 'mail' => $mail],
            'destination@example.org',
        ];
        yield 'open uses mail.destination' => [
            ['eventType' => 'Open', 'mail' => $mail],
            'destination@example.org',
        ];
        yield 'bounce uses bouncedRecipients' => [
            ['eventType' => 'Bounce', 'mail' => $mail, 'bounce' => ['bounceType' => 'Permanent', 'bouncedRecipients' => [['emailAddress' => 'bounced@example.org']]]],
            'bounced@example.org',
        ];
        yield 'complaint uses complainedRecipients' => [
            ['eventType' => 'Complaint', 'mail' => $mail, 'complaint' => ['complainedRecipients' => [['emailAddress' => 'complained@example.org']]]],
            'complained@example.org',
        ];
        yield 'delivery delay uses delayedRecipients' => [
            ['eventType' => 'DeliveryDelay', 'mail' => $mail, 'deliveryDelay' => ['delayedRecipients' => [['emailAddress' => 'delayed@example.org']]]],
            'delayed@example.org',
        ];
        yield 'bounce without specific recipient falls back to destination' => [
            ['eventType' => 'Bounce', 'mail' => $mail, 'bounce' => ['bounceType' => 'Transient']],
            'destination@example.org',
        ];
    }

    /**
     * @param array<string, mixed> $event
     */
    #[DataProvider('provideOccurredAtPerType')]
    public function testOccurredAtReadsTheEventSection(array $event, ?string $expected): void
    {
        $occurredAt = $this->extract($this->snsPayload($event))->occurredAt;

        self::assertSame($expected, $occurredAt?->format('Y-m-d H:i:s'));
    }

    public static function provideOccurredAtPerType(): iterable
    {
        $ts = '2024-01-15T10:30:00.000Z';
        $mailTs = ['mail' => ['timestamp' => '2020-02-02T02:02:02.000Z']];

        yield 'open' => [['eventType' => 'Open', 'open' => ['timestamp' => $ts]] + $mailTs, '2024-01-15 10:30:00'];
        yield 'click' => [['eventType' => 'Click', 'click' => ['timestamp' => $ts]] + $mailTs, '2024-01-15 10:30:00'];
        yield 'delivery' => [['eventType' => 'Delivery', 'delivery' => ['timestamp' => $ts]] + $mailTs, '2024-01-15 10:30:00'];
        yield 'delivery delay' => [['eventType' => 'DeliveryDelay', 'deliveryDelay' => ['timestamp' => $ts]] + $mailTs, '2024-01-15 10:30:00'];
        yield 'bounce' => [['eventType' => 'Bounce', 'bounce' => ['timestamp' => $ts]] + $mailTs, '2024-01-15 10:30:00'];
        yield 'complaint' => [['eventType' => 'Complaint', 'complaint' => ['timestamp' => $ts]] + $mailTs, '2024-01-15 10:30:00'];
        // Send/Reject have no dedicated section: their only timestamp is mail.timestamp.
        yield 'send uses mail.timestamp' => [['eventType' => 'Send'] + $mailTs, '2020-02-02 02:02:02'];
        yield 'reject uses mail.timestamp' => [['eventType' => 'Reject'] + $mailTs, '2020-02-02 02:02:02'];
        // No generic mail.timestamp fallback for other types.
        yield 'delivery without section timestamp is null' => [['eventType' => 'Delivery'] + $mailTs, null];
    }

    public function testMalformedMessageKeepsEnvelopeAndDoesNotThrow(): void
    {
        $payload = ['MessageId' => self::SNS_MESSAGE_ID, 'Message' => 'not-json'];

        $data = $this->extract($payload);

        self::assertSame(self::SNS_MESSAGE_ID, $data->snsMessageId);
        self::assertNull($data->eventType);
        self::assertNull($data->recipient);
        self::assertNull($data->occurredAt);
        self::assertSame($payload, $data->payload);
    }

    public function testNonUuidTagsAreNull(): void
    {
        $data = $this->extract($this->snsPayload([
            'eventType' => 'Open',
            'mail' => ['tags' => ['campaign_uuid' => ['not-a-uuid'], 'adherent_uuid' => ['also-bad']]],
        ]));

        self::assertNull($data->campaignUuid);
        self::assertNull($data->adherentUuid);
    }

    public function testOversizedValuesAreBounded(): void
    {
        $longEventType = str_repeat('A', 200);
        $longRecipient = str_repeat('b', 400).'@example.org';

        $data = $this->extract($this->snsPayload([
            'eventType' => $longEventType,
            'mail' => ['destination' => [$longRecipient]],
        ]));

        self::assertSame(50, mb_strlen((string) $data->eventType));
        self::assertSame(255, mb_strlen((string) $data->recipient));
    }

    public function testMissingSnsMessageIdIsEmptyString(): void
    {
        $data = $this->extract(['Message' => json_encode(['eventType' => 'Open'])]);

        self::assertSame('', $data->snsMessageId);
    }

    public function testPayloadIsTheFullEnvelope(): void
    {
        $payload = $this->snsPayload(['eventType' => 'Delivery', 'mail' => []]);
        $payload['Signature'] = 'abc';
        $payload['TopicArn'] = 'arn:aws:sns:eu-west-3:123:topic';

        self::assertSame($payload, $this->extract($payload)->payload);
    }

    private function extract(array $payload): SesRawEventData
    {
        return new SesRawEventExtractor(new SesPayloadReader())->extract($payload);
    }

    /**
     * @return array<string, list<string>>
     */
    private function tags(): array
    {
        return [
            'campaign_uuid' => [self::CAMPAIGN_UUID],
            'adherent_uuid' => [self::ADHERENT_UUID],
        ];
    }

    /**
     * @param array<string, mixed> $message
     *
     * @return array<string, mixed>
     */
    private function snsPayload(array $message): array
    {
        return ['MessageId' => self::SNS_MESSAGE_ID, 'Type' => 'Notification', 'Message' => json_encode($message)];
    }
}
