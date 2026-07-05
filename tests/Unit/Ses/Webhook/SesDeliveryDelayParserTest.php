<?php

declare(strict_types=1);

namespace Tests\App\Unit\Ses\Webhook;

use App\Ses\Webhook\SesDeliveryDelayEvent;
use App\Ses\Webhook\SesDeliveryDelayParser;
use App\Ses\Webhook\SesPayloadReader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Payloads mirror the AWS SES event-publishing format delivered on SNS (eventType DeliveryDelay,
 * mail.tags as arrays, deliveryDelay.timestamp / deliveryDelay.delayType).
 */
final class SesDeliveryDelayParserTest extends TestCase
{
    private const CAMPAIGN_UUID = '11111111-1111-4111-8111-111111111111';
    private const ADHERENT_UUID = '22222222-2222-4222-8222-222222222222';

    public function testParsesDeliveryDelayEvent(): void
    {
        $event = $this->parse($this->snsPayload([
            'eventType' => 'DeliveryDelay',
            'mail' => ['tags' => $this->tags()],
            'deliveryDelay' => ['timestamp' => '2024-01-15T10:30:00.000Z', 'delayType' => 'Throttling'],
        ]));

        self::assertInstanceOf(SesDeliveryDelayEvent::class, $event);
        self::assertSame(self::CAMPAIGN_UUID, $event->campaignUuid->toRfc4122());
        self::assertSame(self::ADHERENT_UUID, $event->adherentUuid->toRfc4122());
        self::assertSame('2024-01-15 10:30:00', $event->delayedAt->format('Y-m-d H:i:s'));
        self::assertSame('UTC', $event->delayedAt->getTimezone()->getName());
        self::assertSame('Throttling', $event->delayType);
    }

    public function testDelayTypeIsNullWhenAbsent(): void
    {
        $event = $this->parse($this->snsPayload([
            'eventType' => 'DeliveryDelay',
            'mail' => ['tags' => $this->tags()],
            'deliveryDelay' => ['timestamp' => '2024-01-15T10:30:00.000Z'],
        ]));

        self::assertInstanceOf(SesDeliveryDelayEvent::class, $event);
        self::assertNull($event->delayType);
    }

    public function testDelayTypeIsClippedToColumnLength(): void
    {
        $event = $this->parse($this->snsPayload([
            'eventType' => 'DeliveryDelay',
            'mail' => ['tags' => $this->tags()],
            'deliveryDelay' => ['timestamp' => '2024-01-15T10:30:00.000Z', 'delayType' => str_repeat('x', 300)],
        ]));

        self::assertInstanceOf(SesDeliveryDelayEvent::class, $event);
        self::assertSame(255, \strlen((string) $event->delayType));
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
        $delay = ['timestamp' => '2024-01-15T10:30:00Z', 'delayType' => 'Throttling'];

        yield 'delivery event type' => [['Message' => json_encode(['eventType' => 'Delivery', 'mail' => ['tags' => $tags], 'deliveryDelay' => $delay])]];
        yield 'unknown event type' => [['Message' => json_encode(['eventType' => 'Foo', 'mail' => ['tags' => $tags], 'deliveryDelay' => $delay])]];
        yield 'missing campaign tag' => [['Message' => json_encode(['eventType' => 'DeliveryDelay', 'mail' => ['tags' => ['adherent_uuid' => [self::ADHERENT_UUID]]], 'deliveryDelay' => $delay])]];
        yield 'missing adherent tag' => [['Message' => json_encode(['eventType' => 'DeliveryDelay', 'mail' => ['tags' => ['campaign_uuid' => [self::CAMPAIGN_UUID]]], 'deliveryDelay' => $delay])]];
        yield 'invalid campaign uuid' => [['Message' => json_encode(['eventType' => 'DeliveryDelay', 'mail' => ['tags' => ['campaign_uuid' => ['not-a-uuid'], 'adherent_uuid' => [self::ADHERENT_UUID]]], 'deliveryDelay' => $delay])]];
        yield 'delay without timestamp' => [['Message' => json_encode(['eventType' => 'DeliveryDelay', 'mail' => ['tags' => $tags], 'deliveryDelay' => ['delayType' => 'Throttling']])]];
        yield 'message not a string' => [['Message' => ['not', 'a', 'string']]];
        yield 'message not json' => [['Message' => 'this is not json']];
        yield 'message absent' => [[]];
    }

    private function parse(array $payload): ?SesDeliveryDelayEvent
    {
        return new SesDeliveryDelayParser(new SesPayloadReader())->parse($payload);
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
     * @return array{Message: string}
     */
    private function snsPayload(array $message): array
    {
        return ['Type' => 'Notification', 'Message' => json_encode($message)];
    }
}
