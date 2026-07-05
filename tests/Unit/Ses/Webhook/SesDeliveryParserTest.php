<?php

declare(strict_types=1);

namespace Tests\App\Unit\Ses\Webhook;

use App\Ses\Webhook\SesDeliveryEvent;
use App\Ses\Webhook\SesDeliveryParser;
use App\Ses\Webhook\SesPayloadReader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Payloads mirror the AWS SES event-publishing format delivered on SNS (eventType Delivery,
 * mail.tags as arrays, delivery.timestamp).
 */
final class SesDeliveryParserTest extends TestCase
{
    private const CAMPAIGN_UUID = '11111111-1111-4111-8111-111111111111';
    private const ADHERENT_UUID = '22222222-2222-4222-8222-222222222222';

    public function testParsesDeliveryEvent(): void
    {
        $event = $this->parse($this->snsPayload([
            'eventType' => 'Delivery',
            'mail' => ['tags' => $this->tags()],
            'delivery' => ['timestamp' => '2024-01-15T10:30:00.000Z'],
        ]));

        self::assertInstanceOf(SesDeliveryEvent::class, $event);
        self::assertSame(self::CAMPAIGN_UUID, $event->campaignUuid->toRfc4122());
        self::assertSame(self::ADHERENT_UUID, $event->adherentUuid->toRfc4122());
        self::assertSame('2024-01-15 10:30:00', $event->deliveredAt->format('Y-m-d H:i:s'));
        self::assertSame('UTC', $event->deliveredAt->getTimezone()->getName());
    }

    public function testNonUtcTimestampIsNormalisedToUtc(): void
    {
        $event = $this->parse($this->snsPayload([
            'eventType' => 'Delivery',
            'mail' => ['tags' => $this->tags()],
            'delivery' => ['timestamp' => '2024-01-15T12:30:00+02:00'],
        ]));

        self::assertInstanceOf(SesDeliveryEvent::class, $event);
        self::assertSame('2024-01-15 10:30:00', $event->deliveredAt->format('Y-m-d H:i:s'));
    }

    /**
     * @param array<string, mixed> $payload
     */
    #[DataProvider('provideNonActionablePayloads')]
    public function testReturnsNullForNonActionablePayloads(array $payload): void
    {
        self::assertNull($this->parse($payload));
    }

    public static function provideNonActionablePayloads(): iterable
    {
        $tags = ['campaign_uuid' => [self::CAMPAIGN_UUID], 'adherent_uuid' => [self::ADHERENT_UUID]];
        $ts = ['timestamp' => '2024-01-15T10:30:00Z'];

        yield 'open event type' => [['Message' => json_encode(['eventType' => 'Open', 'mail' => ['tags' => $tags], 'delivery' => $ts])]];
        yield 'delivery delay event type' => [['Message' => json_encode(['eventType' => 'DeliveryDelay', 'mail' => ['tags' => $tags], 'delivery' => $ts])]];
        yield 'unknown event type' => [['Message' => json_encode(['eventType' => 'Foo', 'mail' => ['tags' => $tags], 'delivery' => $ts])]];
        yield 'missing campaign tag' => [['Message' => json_encode(['eventType' => 'Delivery', 'mail' => ['tags' => ['adherent_uuid' => [self::ADHERENT_UUID]]], 'delivery' => $ts])]];
        yield 'missing adherent tag' => [['Message' => json_encode(['eventType' => 'Delivery', 'mail' => ['tags' => ['campaign_uuid' => [self::CAMPAIGN_UUID]]], 'delivery' => $ts])]];
        yield 'invalid campaign uuid' => [['Message' => json_encode(['eventType' => 'Delivery', 'mail' => ['tags' => ['campaign_uuid' => ['not-a-uuid'], 'adherent_uuid' => [self::ADHERENT_UUID]]], 'delivery' => $ts])]];
        yield 'tags absent' => [['Message' => json_encode(['eventType' => 'Delivery', 'mail' => [], 'delivery' => $ts])]];
        yield 'delivery without timestamp' => [['Message' => json_encode(['eventType' => 'Delivery', 'mail' => ['tags' => $tags]])]];
        yield 'message not a string' => [['Message' => ['not', 'a', 'string']]];
        yield 'message not json' => [['Message' => 'this is not json']];
        yield 'message absent' => [[]];
    }

    private function parse(array $payload): ?SesDeliveryEvent
    {
        return new SesDeliveryParser(new SesPayloadReader())->parse($payload);
    }

    /**
     * @return array<string, list<string>>
     */
    private function tags(): array
    {
        return [
            'campaign_uuid' => [self::CAMPAIGN_UUID],
            'adherent_uuid' => [self::ADHERENT_UUID],
            'ses:configuration-set' => ['renaissance-staging'],
        ];
    }

    /**
     * @param array<string, mixed> $message
     *
     * @return array{Message: string}
     */
    private function snsPayload(array $message): array
    {
        return ['Type' => 'Notification', 'Message' => json_encode($message)];
    }
}
