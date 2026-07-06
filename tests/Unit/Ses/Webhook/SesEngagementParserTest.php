<?php

declare(strict_types=1);

namespace Tests\App\Unit\Ses\Webhook;

use App\Ses\Webhook\SesEngagementEvent;
use App\Ses\Webhook\SesEngagementParser;
use App\Ses\Webhook\SesEngagementType;
use App\Ses\Webhook\SesPayloadReader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Payloads mirror the AWS SES event-publishing format delivered on SNS (eventType, mail.tags as
 * arrays, open.timestamp / click.timestamp / click.link).
 */
final class SesEngagementParserTest extends TestCase
{
    private const CAMPAIGN_UUID = '11111111-1111-4111-8111-111111111111';
    private const ADHERENT_UUID = '22222222-2222-4222-8222-222222222222';

    public function testParsesOpenEvent(): void
    {
        $event = $this->parse($this->snsPayload([
            'eventType' => 'Open',
            'mail' => ['tags' => $this->tags(), 'timestamp' => '2024-01-15T10:00:00.000Z'],
            'open' => [
                'timestamp' => '2024-01-15T10:30:00.000Z',
                'ipAddress' => '17.58.63.100',
                'userAgent' => 'Mozilla/5.0',
            ],
        ]));

        self::assertNotNull($event);
        self::assertSame(SesEngagementType::OPEN, $event->type);
        self::assertSame(self::CAMPAIGN_UUID, $event->campaignUuid->toRfc4122());
        self::assertSame(self::ADHERENT_UUID, $event->adherentUuid->toRfc4122());
        self::assertSame('2024-01-15 10:30:00', $event->occurredAt->format('Y-m-d H:i:s'));
        self::assertSame('UTC', $event->occurredAt->getTimezone()->getName());
        self::assertNull($event->url);
        self::assertSame('17.58.63.100', $event->ipAddress);
        self::assertSame('Mozilla/5.0', $event->userAgent);
    }

    public function testParsesClickEventWithLink(): void
    {
        $event = $this->parse($this->snsPayload([
            'eventType' => 'Click',
            'mail' => ['tags' => $this->tags()],
            'click' => [
                'timestamp' => '2024-01-15T10:30:00.000Z',
                'link' => 'https://parti-renaissance.fr/x',
                'ipAddress' => '203.0.113.5',
                'userAgent' => 'Mozilla/5.0 (iPhone)',
            ],
        ]));

        self::assertNotNull($event);
        self::assertSame(SesEngagementType::CLICK, $event->type);
        self::assertSame('https://parti-renaissance.fr/x', $event->url);
        self::assertSame('203.0.113.5', $event->ipAddress);
        self::assertSame('Mozilla/5.0 (iPhone)', $event->userAgent);
    }

    public function testOpenWithoutClientSignalsYieldsNulls(): void
    {
        $event = $this->parse($this->snsPayload([
            'eventType' => 'Open',
            'mail' => ['tags' => $this->tags()],
            'open' => ['timestamp' => '2024-01-15T10:30:00.000Z'],
        ]));

        self::assertNotNull($event);
        self::assertNull($event->ipAddress);
        self::assertNull($event->userAgent);
    }

    public function testNonUtcTimestampIsNormalisedToUtc(): void
    {
        $event = $this->parse($this->snsPayload([
            'eventType' => 'Open',
            'mail' => ['tags' => $this->tags()],
            'open' => ['timestamp' => '2024-01-15T12:30:00+02:00'],
        ]));

        self::assertNotNull($event);
        self::assertSame('2024-01-15 10:30:00', $event->occurredAt->format('Y-m-d H:i:s'));
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

        yield 'bounce event type' => [['Message' => json_encode(['eventType' => 'Bounce', 'mail' => ['tags' => $tags]])]];
        yield 'delivery event type' => [['Message' => json_encode(['eventType' => 'Delivery', 'mail' => ['tags' => $tags]])]];
        yield 'unknown event type' => [['Message' => json_encode(['eventType' => 'Foo', 'mail' => ['tags' => $tags]])]];
        yield 'missing campaign tag' => [['Message' => json_encode(['eventType' => 'Open', 'mail' => ['tags' => ['adherent_uuid' => [self::ADHERENT_UUID]]], 'open' => ['timestamp' => '2024-01-15T10:30:00Z']])]];
        yield 'missing adherent tag' => [['Message' => json_encode(['eventType' => 'Open', 'mail' => ['tags' => ['campaign_uuid' => [self::CAMPAIGN_UUID]]], 'open' => ['timestamp' => '2024-01-15T10:30:00Z']])]];
        yield 'invalid campaign uuid' => [['Message' => json_encode(['eventType' => 'Open', 'mail' => ['tags' => ['campaign_uuid' => ['not-a-uuid'], 'adherent_uuid' => [self::ADHERENT_UUID]]], 'open' => ['timestamp' => '2024-01-15T10:30:00Z']])]];
        yield 'tags absent' => [['Message' => json_encode(['eventType' => 'Open', 'mail' => [], 'open' => ['timestamp' => '2024-01-15T10:30:00Z']])]];
        yield 'open without timestamp' => [['Message' => json_encode(['eventType' => 'Open', 'mail' => ['tags' => $tags]])]];
        yield 'click without link' => [['Message' => json_encode(['eventType' => 'Click', 'mail' => ['tags' => $tags], 'click' => ['timestamp' => '2024-01-15T10:30:00Z']])]];
        yield 'click with empty link' => [['Message' => json_encode(['eventType' => 'Click', 'mail' => ['tags' => $tags], 'click' => ['timestamp' => '2024-01-15T10:30:00Z', 'link' => '']])]];
        yield 'message not a string' => [['Message' => ['not', 'a', 'string']]];
        yield 'message not json' => [['Message' => 'this is not json']];
        yield 'message absent' => [[]];
    }

    private function parse(array $payload): ?SesEngagementEvent
    {
        return new SesEngagementParser(new SesPayloadReader())->parse($payload);
    }

    /**
     * @return array<string, list<string>>
     */
    private function tags(): array
    {
        return [
            'campaign_uuid' => [self::CAMPAIGN_UUID],
            'adherent_uuid' => [self::ADHERENT_UUID],
            'ses:configuration-set' => ['renaissance-publications'],
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
