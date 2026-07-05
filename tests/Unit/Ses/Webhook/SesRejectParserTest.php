<?php

declare(strict_types=1);

namespace Tests\App\Unit\Ses\Webhook;

use App\Ses\Webhook\SesPayloadReader;
use App\Ses\Webhook\SesRejectEvent;
use App\Ses\Webhook\SesRejectParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Payloads mirror the AWS SES event-publishing Reject event (eventType Reject, mail.tags, mail.timestamp,
 * reject.reason). Reject has no timestamp of its own, so mail.timestamp is used.
 */
final class SesRejectParserTest extends TestCase
{
    private const CAMPAIGN_UUID = '11111111-1111-4111-8111-111111111111';
    private const ADHERENT_UUID = '22222222-2222-4222-8222-222222222222';

    public function testParsesRejectEvent(): void
    {
        $event = $this->parse($this->snsPayload([
            'eventType' => 'Reject',
            'mail' => ['tags' => $this->tags(), 'timestamp' => '2024-01-15T10:30:00.000Z'],
            'reject' => ['reason' => 'Bad content'],
        ]));

        self::assertInstanceOf(SesRejectEvent::class, $event);
        self::assertSame(self::CAMPAIGN_UUID, $event->campaignUuid->toRfc4122());
        self::assertSame(self::ADHERENT_UUID, $event->adherentUuid->toRfc4122());
        self::assertSame('2024-01-15 10:30:00', $event->rejectedAt->format('Y-m-d H:i:s'));
        self::assertSame('UTC', $event->rejectedAt->getTimezone()->getName());
        self::assertSame('Bad content', $event->reason);
    }

    public function testReasonDegradesToNullWhenAbsent(): void
    {
        $event = $this->parse($this->snsPayload([
            'eventType' => 'Reject',
            'mail' => ['tags' => $this->tags(), 'timestamp' => '2024-01-15T10:30:00.000Z'],
        ]));

        self::assertInstanceOf(SesRejectEvent::class, $event);
        self::assertNull($event->reason);
    }

    public function testOverlongReasonIsClipped(): void
    {
        $event = $this->parse($this->snsPayload([
            'eventType' => 'Reject',
            'mail' => ['tags' => $this->tags(), 'timestamp' => '2024-01-15T10:30:00.000Z'],
            'reject' => ['reason' => str_repeat('x', 300)],
        ]));

        self::assertInstanceOf(SesRejectEvent::class, $event);
        self::assertSame(255, \strlen((string) $event->reason));
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
        $ts = '2024-01-15T10:30:00Z';

        yield 'other event type' => [['Message' => json_encode(['eventType' => 'Delivery', 'mail' => ['tags' => $tags, 'timestamp' => $ts]])]];
        yield 'missing campaign tag' => [['Message' => json_encode(['eventType' => 'Reject', 'mail' => ['tags' => ['adherent_uuid' => [self::ADHERENT_UUID]], 'timestamp' => $ts]])]];
        yield 'missing adherent tag' => [['Message' => json_encode(['eventType' => 'Reject', 'mail' => ['tags' => ['campaign_uuid' => [self::CAMPAIGN_UUID]], 'timestamp' => $ts]])]];
        yield 'tags absent (direct-identity style)' => [['Message' => json_encode(['eventType' => 'Reject', 'mail' => ['timestamp' => $ts]])]];
        yield 'malformed tags (scalar)' => [['Message' => json_encode(['eventType' => 'Reject', 'mail' => ['tags' => 'oops', 'timestamp' => $ts]])]];
        yield 'missing timestamp' => [['Message' => json_encode(['eventType' => 'Reject', 'mail' => ['tags' => $tags]])]];
        yield 'message not json' => [['Message' => 'this is not json']];
        yield 'message absent' => [[]];
    }

    private function parse(array $payload): ?SesRejectEvent
    {
        return new SesRejectParser(new SesPayloadReader())->parse($payload);
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
     * @return array{Type: string, Message: string}
     */
    private function snsPayload(array $message): array
    {
        return ['Type' => 'Notification', 'Message' => json_encode($message)];
    }
}
