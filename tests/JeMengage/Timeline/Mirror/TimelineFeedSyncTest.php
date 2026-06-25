<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\Mirror;

use App\JeMengage\Timeline\Mirror\TimelineFeedDocument;
use App\JeMengage\Timeline\Mirror\TimelineFeedTransformer;
use App\JeMengage\Timeline\Mirror\TimelineFeedWriter;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\Group;
use Psr\Log\NullLogger;
use Symfony\Component\Uid\Uuid;
use Tests\App\AbstractKernelTestCase;

/**
 * End-to-end of the sync column wiring: the transformer projects visibility/committee_uuid/agora_uuid
 * from the normalizer record, the document carries them, and the writer persists them as columns.
 */
#[Group('functional')]
final class TimelineFeedSyncTest extends AbstractKernelTestCase
{
    private ?Connection $connection = null;
    private ?TimelineFeedWriter $writer = null;
    private ?TimelineFeedTransformer $transformer = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->manager->getConnection();
        $this->writer = new TimelineFeedWriter($this->connection, new NullLogger());
        $this->transformer = new TimelineFeedTransformer();

        // Isolate this test: the DELETE and the writer's raw-DBAL upserts run inside a transaction
        // rolled back in tearDown, so the fixture timeline_feed rows are restored for other tests.
        $this->connection->beginTransaction();
        $this->connection->executeStatement('DELETE FROM timeline_feed');
    }

    protected function tearDown(): void
    {
        $this->connection->rollBack();
        $this->connection = null;
        $this->writer = null;
        $this->transformer = null;

        parent::tearDown();
    }

    public function testPublicEventPopulatesVisibilityWithoutCommitteeOrAgora(): void
    {
        $uuid = Uuid::v4();
        $this->writer->upsert($this->documentFor($uuid, [
            'type' => 'event', 'visibility' => 'public', 'committee_uuid' => null, 'agora_uuid' => null,
        ]));

        $row = $this->row($uuid);
        self::assertSame('public', $row['visibility']);
        self::assertNull($row['committee_uuid']);
        self::assertNull($row['agora_uuid']);
    }

    public function testCommitteeEventPopulatesCommitteeUuid(): void
    {
        $uuid = Uuid::v4();
        $this->writer->upsert($this->documentFor($uuid, [
            'type' => 'event', 'visibility' => 'public', 'committee_uuid' => 'committee-123', 'agora_uuid' => null,
        ]));

        self::assertSame('committee-123', $this->row($uuid)['committee_uuid']);
    }

    public function testAgoraEventPopulatesAgoraUuid(): void
    {
        $uuid = Uuid::v4();
        $this->writer->upsert($this->documentFor($uuid, [
            'type' => 'event', 'visibility' => 'public', 'committee_uuid' => null, 'agora_uuid' => 'agora-456',
        ]));

        self::assertSame('agora-456', $this->row($uuid)['agora_uuid']);
    }

    public function testSocialPostLeavesDenormalizedColumnsNull(): void
    {
        $uuid = Uuid::v4();
        $this->writer->upsert($this->documentFor($uuid, [
            'type' => 'social_network_post', 'visibility' => null, 'committee_uuid' => null, 'agora_uuid' => null,
        ]));

        $row = $this->row($uuid);
        self::assertNull($row['visibility']);
        self::assertNull($row['committee_uuid']);
        self::assertNull($row['agora_uuid']);
    }

    public function testReUpsertUpdatesDenormalizedColumns(): void
    {
        $uuid = Uuid::v4();
        $this->writer->upsert($this->documentFor($uuid, [
            'type' => 'event', 'visibility' => 'adherent', 'committee_uuid' => 'committee-1', 'agora_uuid' => null,
        ]));
        $this->writer->upsert($this->documentFor($uuid, [
            'type' => 'event', 'visibility' => 'public', 'committee_uuid' => null, 'agora_uuid' => null,
        ]));

        $row = $this->row($uuid);
        self::assertSame('public', $row['visibility']);
        self::assertNull($row['committee_uuid']);
    }

    /**
     * Builds the mirror document the way the resolver does: transform the normalizer record, then
     * carry the derived fields into the document.
     *
     * @param array<string, mixed> $overrides
     */
    private function documentFor(Uuid $uuid, array $overrides): TimelineFeedDocument
    {
        $display = array_merge([
            'objectID' => $uuid->toRfc4122(),
            'type' => 'event',
            'title' => 'Item',
            'date' => '2026-05-20T10:00:00+00:00',
            'visibility' => null,
            'committee_uuid' => null,
            'agora_uuid' => null,
        ], $overrides);

        $canonical = $this->transformer->transform($display);

        return new TimelineFeedDocument(
            $uuid,
            $canonical['type'],
            $canonical['publicationDate'],
            $canonical['eventDate'],
            $canonical['audience'],
            $canonical['display'],
            $canonical['visibility'],
            $canonical['committeeUuid'],
            $canonical['agoraUuid'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function row(Uuid $uuid): array
    {
        return $this->connection->fetchAssociative(
            'SELECT * FROM timeline_feed WHERE uuid = :uuid',
            ['uuid' => $uuid->toRfc4122()],
        );
    }
}
