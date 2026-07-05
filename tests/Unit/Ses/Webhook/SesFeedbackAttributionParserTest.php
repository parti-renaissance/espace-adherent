<?php

declare(strict_types=1);

namespace Tests\App\Unit\Ses\Webhook;

use App\Ses\Webhook\SesFeedbackAttributionEvent;
use App\Ses\Webhook\SesFeedbackAttributionParser;
use App\Ses\Webhook\SesFeedbackType;
use App\Ses\Webhook\SesPayloadReader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Payloads mirror the AWS SES event-publishing Bounce/Complaint events (eventType present, mail.tags,
 * bounce.bounceType/bounceSubType/timestamp, complaint.timestamp). Only Permanent bounces are attributed,
 * matching the actionable scope of the global feedback path; the tag-less direct-identity path is ignored.
 */
final class SesFeedbackAttributionParserTest extends TestCase
{
    private const CAMPAIGN_UUID = '11111111-1111-4111-8111-111111111111';
    private const ADHERENT_UUID = '22222222-2222-4222-8222-222222222222';

    public function testParsesPermanentBounce(): void
    {
        $event = $this->parse($this->snsPayload([
            'eventType' => 'Bounce',
            'mail' => ['tags' => $this->tags()],
            'bounce' => ['bounceType' => 'Permanent', 'bounceSubType' => 'General', 'timestamp' => '2024-01-15T10:30:00.000Z'],
        ]));

        self::assertInstanceOf(SesFeedbackAttributionEvent::class, $event);
        self::assertSame(SesFeedbackType::HARD_BOUNCE, $event->type);
        self::assertSame(self::CAMPAIGN_UUID, $event->campaignUuid->toRfc4122());
        self::assertSame(self::ADHERENT_UUID, $event->adherentUuid->toRfc4122());
        self::assertSame('2024-01-15 10:30:00', $event->occurredAt->format('Y-m-d H:i:s'));
        self::assertSame('General', $event->bounceSubType);
    }

    public function testParsesComplaint(): void
    {
        $event = $this->parse($this->snsPayload([
            'eventType' => 'Complaint',
            'mail' => ['tags' => $this->tags()],
            'complaint' => ['timestamp' => '2024-01-15T10:30:00.000Z'],
        ]));

        self::assertInstanceOf(SesFeedbackAttributionEvent::class, $event);
        self::assertSame(SesFeedbackType::COMPLAINT, $event->type);
        self::assertNull($event->bounceSubType);
    }

    public function testOverlongBounceSubTypeIsClipped(): void
    {
        $event = $this->parse($this->snsPayload([
            'eventType' => 'Bounce',
            'mail' => ['tags' => $this->tags()],
            'bounce' => ['bounceType' => 'Permanent', 'bounceSubType' => str_repeat('x', 300), 'timestamp' => '2024-01-15T10:30:00.000Z'],
        ]));

        self::assertInstanceOf(SesFeedbackAttributionEvent::class, $event);
        self::assertSame(255, \strlen((string) $event->bounceSubType));
    }

    /**
     * @param array<string, mixed> $payload
     */
    #[DataProvider('provideNonAttributablePayloads')]
    public function testReturnsNullForNonAttributablePayloads(array $payload): void
    {
        self::assertNull($this->parse($payload));
    }

    public static function provideNonAttributablePayloads(): iterable
    {
        $tags = ['campaign_uuid' => [self::CAMPAIGN_UUID], 'adherent_uuid' => [self::ADHERENT_UUID]];
        $bounceTs = ['timestamp' => '2024-01-15T10:30:00Z'];

        yield 'transient bounce ignored' => [['Message' => json_encode(['eventType' => 'Bounce', 'mail' => ['tags' => $tags], 'bounce' => ['bounceType' => 'Transient'] + $bounceTs])]];
        yield 'undetermined bounce ignored' => [['Message' => json_encode(['eventType' => 'Bounce', 'mail' => ['tags' => $tags], 'bounce' => ['bounceType' => 'Undetermined'] + $bounceTs])]];
        yield 'delivery event type' => [['Message' => json_encode(['eventType' => 'Delivery', 'mail' => ['tags' => $tags]])]];
        yield 'direct-identity (no eventType)' => [['Message' => json_encode(['notificationType' => 'Bounce', 'mail' => ['tags' => $tags], 'bounce' => ['bounceType' => 'Permanent'] + $bounceTs])]];
        yield 'missing campaign tag' => [['Message' => json_encode(['eventType' => 'Bounce', 'mail' => ['tags' => ['adherent_uuid' => [self::ADHERENT_UUID]]], 'bounce' => ['bounceType' => 'Permanent'] + $bounceTs])]];
        yield 'missing adherent tag' => [['Message' => json_encode(['eventType' => 'Complaint', 'mail' => ['tags' => ['campaign_uuid' => [self::CAMPAIGN_UUID]]], 'complaint' => $bounceTs])]];
        yield 'malformed tags (scalar)' => [['Message' => json_encode(['eventType' => 'Bounce', 'mail' => ['tags' => 'oops'], 'bounce' => ['bounceType' => 'Permanent'] + $bounceTs])]];
        yield 'bounce without timestamp' => [['Message' => json_encode(['eventType' => 'Bounce', 'mail' => ['tags' => $tags], 'bounce' => ['bounceType' => 'Permanent']])]];
        yield 'complaint without timestamp' => [['Message' => json_encode(['eventType' => 'Complaint', 'mail' => ['tags' => $tags], 'complaint' => []])]];
        yield 'message not json' => [['Message' => 'this is not json']];
        yield 'message absent' => [[]];
    }

    private function parse(array $payload): ?SesFeedbackAttributionEvent
    {
        return new SesFeedbackAttributionParser(new SesPayloadReader())->parse($payload);
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
