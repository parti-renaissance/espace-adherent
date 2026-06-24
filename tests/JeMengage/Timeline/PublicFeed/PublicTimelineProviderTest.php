<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\PublicFeed;

use App\Entity\Geo\Zone;
use App\Entity\Timeline\TimelineFeed;
use App\Entity\Timeline\TimelineHiddenFeed;
use App\JeMengage\Timeline\PublicFeed\PublicTimelineProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Uid\Uuid;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
final class PublicTimelineProviderTest extends AbstractKernelTestCase
{
    private ?PublicTimelineProvider $provider = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = static::getContainer()->get(PublicTimelineProvider::class);

        // Isolate the seeded mirror rows: rolled back in tearDown, no fixture pollution.
        $this->manager->getConnection()->beginTransaction();
        $this->manager->getConnection()->executeStatement('DELETE FROM timeline_feed');
    }

    protected function tearDown(): void
    {
        $this->manager->getConnection()->rollBack();
        $this->provider = null;

        parent::tearDown();
    }

    public function testOnlyPublicSocialEventAndActionItemsAreReturned(): void
    {
        // Included (newest first by publication date).
        $this->seed('social', 'social_network_post', '2026-05-23 10:00:00');
        $this->seed('action', 'action', '2026-05-22 10:00:00');
        $this->seed('event-public', 'event', '2026-05-21 10:00:00', visibility: 'public');

        // Excluded.
        $this->seed('event-committee', 'event', '2026-05-24 10:00:00', visibility: 'public', committeeUuid: Uuid::v4()->toRfc4122());
        $this->seed('event-agora', 'event', '2026-05-24 10:00:00', visibility: 'public', agoraUuid: Uuid::v4()->toRfc4122());
        $this->seed('event-adherent', 'event', '2026-05-24 10:00:00', visibility: 'adherent');
        $hidden = $this->seed('event-hidden', 'event', '2026-05-25 10:00:00', visibility: 'public');
        $this->hide($hidden);

        $this->manager->flush();

        $result = $this->provider->findItems(0);

        self::assertSame(['social', 'action', 'event-public'], array_column($result['hits'], 'identifier'));
        self::assertSame(3, $result['nbHits']);
        self::assertSame(0, $result['page']);
        self::assertSame(1, $result['nbPages']);
        self::assertSame(10, $result['hitsPerPage']);
    }

    public function testInternalDisplayFieldsAreSanitizedOut(): void
    {
        $this->seed('social', 'social_network_post', '2026-05-23 10:00:00');
        $this->manager->flush();

        $hit = $this->provider->findItems(0)['hits'][0];

        self::assertArrayHasKey('identifier', $hit);
        foreach (['adherent_ids', 'zone_codes', 'audience', 'access', 'committee_uuid'] as $internal) {
            self::assertArrayNotHasKey($internal, $hit);
        }
    }

    public function testSecondPageIsEmptyWhenItemsFitOnFirstPage(): void
    {
        $this->seed('social', 'social_network_post', '2026-05-23 10:00:00');
        $this->manager->flush();

        $result = $this->provider->findItems(1);

        self::assertSame([], $result['hits']);
        self::assertSame(0, $result['nbHits']);
        // The total count is page-independent: one item overall.
        self::assertSame(1, $result['nbPages']);
    }

    public function testZoneFilterKeepsZoneAndNationalItemsOnly(): void
    {
        $this->seed('paris-action', 'action', '2026-05-23 10:00:00', zoneCodes: ['city_75056']);
        $this->seed('marseille-action', 'action', '2026-05-22 10:00:00', zoneCodes: ['city_13055']);
        $this->seed('national-social', 'social_network_post', '2026-05-21 10:00:00', national: true);
        $this->manager->flush();

        $paris = new Zone(Zone::CITY, '75056', 'Paris');
        $identifiers = array_column($this->provider->findItems(0, $paris)['hits'], 'identifier');

        sort($identifiers);
        self::assertSame(['national-social', 'paris-action'], $identifiers);
    }

    /**
     * @param string[] $zoneCodes
     */
    private function seed(
        string $identifier,
        string $type,
        string $date,
        ?string $visibility = null,
        ?string $committeeUuid = null,
        ?string $agoraUuid = null,
        array $zoneCodes = [],
        bool $national = false,
    ): string {
        $feed = new TimelineFeed();
        new \ReflectionProperty(TimelineFeed::class, 'uuid')->setValue($feed, Uuid::v4());
        $feed->type = $type;
        $feed->publicationDate = new \DateTimeImmutable($date);
        $feed->visibility = $visibility;
        $feed->committeeUuid = $committeeUuid;
        $feed->agoraUuid = $agoraUuid;
        $feed->display = [
            'identifier' => $identifier,
            'type' => $type,
            'title' => 'Item '.$identifier,
            'zone_codes' => $zoneCodes,
            'is_national' => $national,
            'adherent_ids' => [1, 2],
            'access' => ['author_id' => 1],
        ];
        $feed->updatedAt = new \DateTimeImmutable($date);

        $this->manager->persist($feed);

        return $feed->getUuid()->toRfc4122();
    }

    private function hide(string $uuid): void
    {
        $this->manager->persist(new TimelineHiddenFeed(Uuid::fromString($uuid)));
    }
}
