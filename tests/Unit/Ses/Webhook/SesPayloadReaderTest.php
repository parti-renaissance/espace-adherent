<?php

declare(strict_types=1);

namespace Tests\App\Unit\Ses\Webhook;

use App\Ses\Webhook\SesPayloadReader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class SesPayloadReaderTest extends TestCase
{
    private const UUID = '11111111-1111-4111-8111-111111111111';
    private const ADHERENT_UUID = '22222222-2222-4222-8222-222222222222';

    private SesPayloadReader $reader;

    protected function setUp(): void
    {
        $this->reader = new SesPayloadReader();
    }

    public function testDecodeReturnsTheSesEvent(): void
    {
        $event = $this->reader->decode(['Message' => json_encode(['eventType' => 'Delivery'])]);

        self::assertSame(['eventType' => 'Delivery'], $event);
    }

    #[DataProvider('provideUndecodableMessages')]
    public function testDecodeReturnsEmptyArrayWhenMessageIsNotADecodableEvent(mixed $message): void
    {
        self::assertSame([], $this->reader->decode(['Message' => $message]));
    }

    public static function provideUndecodableMessages(): iterable
    {
        yield 'message null' => [null];
        yield 'message not a string' => [['not', 'a', 'string']];
        yield 'message not json' => ['this is not json'];
        yield 'json scalar, not an object' => ['42'];
    }

    public function testDecodeReturnsEmptyArrayWhenEnvelopeHasNoMessage(): void
    {
        self::assertSame([], $this->reader->decode([]));
    }

    public function testReadUuidTagReturnsUuidFromListWrappedTag(): void
    {
        $uuid = $this->reader->readUuidTag(['campaign_uuid' => [self::UUID]], 'campaign_uuid');

        self::assertInstanceOf(Uuid::class, $uuid);
        self::assertSame(self::UUID, $uuid->toRfc4122());
    }

    /**
     * @param array<string, mixed> $tags
     */
    #[DataProvider('provideInvalidUuidTags')]
    public function testReadUuidTagReturnsNullForInvalidTag(array $tags): void
    {
        self::assertNull($this->reader->readUuidTag($tags, 'campaign_uuid'));
    }

    public static function provideInvalidUuidTags(): iterable
    {
        yield 'tag absent' => [[]];
        yield 'empty list' => [['campaign_uuid' => []]];
        yield 'not a uuid' => [['campaign_uuid' => ['not-a-uuid']]];
        yield 'not a string' => [['campaign_uuid' => [1234]]];
        yield 'not list-wrapped' => [['campaign_uuid' => self::UUID]];
        yield 'tag value is a scalar' => [['campaign_uuid' => 1234]];
    }

    /**
     * A malformed "mail.tags" that is not an array (e.g. SES sent a scalar) must degrade to null,
     * not raise a TypeError at the argument boundary.
     */
    #[DataProvider('provideNonArrayTags')]
    public function testReadUuidTagReturnsNullWhenTagsIsNotAnArray(mixed $tags): void
    {
        self::assertNull($this->reader->readUuidTag($tags, 'campaign_uuid'));
        self::assertNull($this->reader->readUuidStringTag($tags, 'campaign_uuid'));
    }

    public static function provideNonArrayTags(): iterable
    {
        yield 'string' => ['not-an-array'];
        yield 'int' => [1234];
        yield 'bool' => [true];
        yield 'null' => [null];
    }

    public function testClipReturnsStringUnchangedWhenShorterThanMax(): void
    {
        self::assertSame('short', $this->reader->clip('short', 64));
    }

    public function testClipTruncatesToMaxLength(): void
    {
        self::assertSame('abcde', $this->reader->clip('abcdefghij', 5));
    }

    #[DataProvider('provideNonClippableValues')]
    public function testClipReturnsNullForEmptyOrNonString(mixed $value): void
    {
        self::assertNull($this->reader->clip($value, 64));
    }

    public static function provideNonClippableValues(): iterable
    {
        yield 'null' => [null];
        yield 'empty string' => [''];
        yield 'int' => [1234];
        yield 'array' => [['x']];
    }

    public function testReadUuidStringTagReturnsRawStringValue(): void
    {
        self::assertSame(self::UUID, $this->reader->readUuidStringTag(['campaign_uuid' => [self::UUID]], 'campaign_uuid'));
    }

    public function testReadUuidStringTagReturnsNullForInvalidTag(): void
    {
        self::assertNull($this->reader->readUuidStringTag(['campaign_uuid' => ['not-a-uuid']], 'campaign_uuid'));
    }

    public function testReadAttributionReturnsBothUuidsFromMailTags(): void
    {
        $attribution = $this->reader->readAttribution([
            'mail' => ['tags' => [
                'campaign_uuid' => [self::UUID],
                'adherent_uuid' => [self::ADHERENT_UUID],
            ]],
        ]);

        self::assertNotNull($attribution);
        self::assertSame(self::UUID, $attribution->campaignUuid->toRfc4122());
        self::assertSame(self::ADHERENT_UUID, $attribution->adherentUuid->toRfc4122());
    }

    /**
     * @param array<string, mixed> $decodedEvent
     */
    #[DataProvider('provideEventsWithoutFullAttribution')]
    public function testReadAttributionReturnsNullWhenEitherUuidIsMissingOrInvalid(array $decodedEvent): void
    {
        self::assertNull($this->reader->readAttribution($decodedEvent));
    }

    public static function provideEventsWithoutFullAttribution(): iterable
    {
        yield 'no mail key' => [['eventType' => 'Delivery']];
        yield 'no tags' => [['mail' => []]];
        yield 'campaign only' => [['mail' => ['tags' => ['campaign_uuid' => [self::UUID]]]]];
        yield 'adherent only' => [['mail' => ['tags' => ['adherent_uuid' => [self::ADHERENT_UUID]]]]];
        yield 'campaign invalid' => [['mail' => ['tags' => ['campaign_uuid' => ['nope'], 'adherent_uuid' => [self::ADHERENT_UUID]]]]];
    }

    public function testReadTimestampNormalizesToUtc(): void
    {
        $ts = $this->reader->readTimestamp('2024-01-15T12:30:00+02:00');

        self::assertInstanceOf(\DateTimeImmutable::class, $ts);
        self::assertSame('2024-01-15 10:30:00', $ts->format('Y-m-d H:i:s'));
        self::assertSame('UTC', $ts->getTimezone()->getName());
    }

    #[DataProvider('provideInvalidTimestamps')]
    public function testReadTimestampReturnsNullForInvalidInput(mixed $raw): void
    {
        self::assertNull($this->reader->readTimestamp($raw));
    }

    public static function provideInvalidTimestamps(): iterable
    {
        yield 'null' => [null];
        yield 'empty string' => [''];
        yield 'not a string' => [1234];
        yield 'unparseable' => ['not-a-date'];
    }
}
