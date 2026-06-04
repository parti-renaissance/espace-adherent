<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\Indexer;

use App\Entity\Timeline\TimelineFeed;
use App\JeMengage\Timeline\Indexer\IndexerPayloadFactory;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Uid\Uuid;

class IndexerPayloadFactoryTest extends TestCase
{
    private const UUID = '11111111-1111-1111-1111-111111111111';

    private IndexerPayloadFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new IndexerPayloadFactory(new NullLogger());
    }

    public function testFullAudienceIsSentAsIs(): void
    {
        $include = [
            'zones' => ['department:75'],
            'adherent_ids' => [1, 2],
            'national' => true,
            'committees' => ['c-uuid'],
            'agoras' => ['a-uuid'],
            'mandate_types' => ['maire'],
        ];

        $payload = $this->factory->create($this->row(TimelineFeedTypeEnum::EVENT, ['include' => $include]));

        self::assertNotNull($payload);
        $json = $payload->jsonSerialize();
        self::assertSame('event', $json['kind']);
        self::assertSame(self::UUID, $json['external_id']);
        // Every dimension is forwarded; the indexer decides what it can match.
        self::assertSame($include, $json['audience']['include']);
        self::assertNull($json['attendance']);
        self::assertSame(['likes' => 0, 'comments' => 0], $json['engagement']);
    }

    public function testNationalFlagIsForwarded(): void
    {
        $payload = $this->factory->create($this->row(TimelineFeedTypeEnum::NEWS, ['include' => ['national' => true]]));

        self::assertSame(['national' => true], $payload->jsonSerialize()['audience']['include']);
    }

    public function testEveryZoneTypeIsForwarded(): void
    {
        $payload = $this->factory->create($this->row(TimelineFeedTypeEnum::NEWS, [
            'include' => ['zones' => ['department:75', 'city_community:69123']],
        ]));

        self::assertSame(['department:75', 'city_community:69123'], $payload->jsonSerialize()['audience']['include']['zones']);
    }

    public function testExcludeIsForwarded(): void
    {
        $payload = $this->factory->create($this->row(TimelineFeedTypeEnum::PUBLICATION, [
            'exclude' => ['mandate_types' => ['maire'], 'tags' => ['senior']],
        ]));

        self::assertSame(['mandate_types' => ['maire'], 'tags' => ['senior']], $payload->jsonSerialize()['audience']['exclude']);
    }

    public function testNullAudienceStaysNull(): void
    {
        $payload = $this->factory->create($this->row(TimelineFeedTypeEnum::EVENT, null));

        self::assertNull($payload->jsonSerialize()['audience']);
    }

    public function testAuthorImportanceIsClampedToIndexerRange(): void
    {
        self::assertSame(1, $this->factory->create($this->row(TimelineFeedTypeEnum::EVENT, null, 0))->authorImportance);
        self::assertSame(5, $this->factory->create($this->row(TimelineFeedTypeEnum::EVENT, null, 9))->authorImportance);
    }

    public function testNonPushableTypeReturnsNull(): void
    {
        self::assertNull($this->factory->create($this->row(TimelineFeedTypeEnum::TRANSACTIONAL_MESSAGE)));
    }

    public function testDatesAreSerializedAsUtcZ(): void
    {
        $payload = $this->factory->create(
            $this->row(TimelineFeedTypeEnum::EVENT, null, 1, new \DateTimeImmutable('2026-06-01T18:00:00+02:00')),
        );

        $json = $payload->jsonSerialize();
        self::assertSame('2026-05-20T10:00:00Z', $json['publication_date']); // 12:00 +02:00 -> 10:00 UTC
        self::assertSame('2026-06-01T16:00:00Z', $json['event_date']);
    }

    private function row(string $type, ?array $audience = null, int $authorImportance = 1, ?\DateTimeImmutable $eventDate = null): TimelineFeed
    {
        $row = new TimelineFeed();
        new \ReflectionProperty(TimelineFeed::class, 'uuid')->setValue($row, Uuid::fromString(self::UUID));
        $row->type = $type;
        $row->publicationDate = new \DateTimeImmutable('2026-05-20T12:00:00+02:00');
        $row->eventDate = $eventDate;
        $row->audience = $audience;
        $row->authorImportance = $authorImportance;
        $row->display = ['objectID' => self::UUID];

        return $row;
    }
}
