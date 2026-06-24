<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline;

use Algolia\SearchBundle\SearchService;
use App\Entity\Adherent;
use App\Entity\Agora;
use App\Entity\AgoraMembership;
use App\Entity\Algolia\AlgoliaJeMengageTimelineFeed;
use App\Entity\CommitteeMembership;
use App\Entity\Timeline\TimelineHiddenFeed;
use App\JeMengage\Timeline\DataProvider;
use App\JeMengage\Timeline\FeedProcessorPipeline;
use App\Repository\Timeline\TimelineHiddenFeedRepository;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Uid\Uuid;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
final class DataProviderTest extends AbstractKernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Isolate the inserted hidden rows: rolled back in tearDown, no fixture pollution.
        $this->manager->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->manager->getConnection()->rollBack();

        parent::tearDown();
    }

    public function testFindItemsFiltersHiddenHits(): void
    {
        $allowed = Uuid::v4()->toRfc4122();
        $hidden = Uuid::v4()->toRfc4122();
        $this->hide($hidden);

        $result = $this->find($this->adherent(), [
            ['objectID' => $allowed, 'type' => 'event'],
            ['objectID' => $hidden, 'type' => 'event'],
        ]);

        self::assertSame([$allowed], array_column($result['hits'], 'objectID'));
    }

    public function testFindItemsKeepsCommitteeEventForMember(): void
    {
        $committeeUuid = Uuid::v4()->toRfc4122();

        $result = $this->find($this->adherent($committeeUuid), [
            ['objectID' => 'a', 'type' => 'event', 'committee_uuid' => $committeeUuid],
        ]);

        self::assertSame(['a'], array_column($result['hits'], 'objectID'));
    }

    public function testFindItemsDropsCommitteeEventForNonMember(): void
    {
        $result = $this->find($this->adherent(Uuid::v4()->toRfc4122()), [
            ['objectID' => 'committee', 'type' => 'event', 'committee_uuid' => Uuid::v4()->toRfc4122()],
            ['objectID' => 'open', 'type' => 'event'],
        ]);

        self::assertSame(['open'], array_column($result['hits'], 'objectID'));
    }

    public function testFindItemsDropsAgoraEventForNonMember(): void
    {
        $result = $this->find($this->adherent(), [
            ['objectID' => 'agora', 'type' => 'event', 'agora_uuid' => Uuid::v4()->toRfc4122()],
            ['objectID' => 'open', 'type' => 'event'],
        ]);

        self::assertSame(['open'], array_column($result['hits'], 'objectID'));
    }

    public function testFindItemsKeepsAgoraEventForMember(): void
    {
        $agoraUuid = Uuid::v4()->toRfc4122();

        $result = $this->find($this->adherent(null, [$agoraUuid]), [
            ['objectID' => 'agora', 'type' => 'event', 'agora_uuid' => $agoraUuid],
        ]);

        self::assertSame(['agora'], array_column($result['hits'], 'objectID'));
    }

    /**
     * @param array<int, array<string, mixed>> $hits
     *
     * @return array<string, mixed>
     */
    private function find(Adherent $user, array $hits): array
    {
        $search = $this->createMock(SearchService::class);
        $search
            ->expects(self::once())
            ->method('rawSearch')
            ->with(AlgoliaJeMengageTimelineFeed::class, '', self::anything())
            ->willReturn(['hits' => $hits, 'nbHits' => \count($hits)])
        ;

        $provider = new DataProvider(
            $search,
            new FeedProcessorPipeline([]),
            static::getContainer()->get(TimelineHiddenFeedRepository::class),
        );

        return $provider->findItems($user, 0, [], []);
    }

    /**
     * A usable Adherent without the heavy constructor: only the membership state the fallback filter
     * reads is set (committee membership + agora memberships, both initialized collections).
     *
     * @param string[] $agoraUuids
     */
    private function adherent(?string $committeeUuid = null, array $agoraUuids = []): Adherent
    {
        $user = new \ReflectionClass(Adherent::class)->newInstanceWithoutConstructor();

        $committeeMembership = null;
        if (null !== $committeeUuid) {
            $committeeMembership = $this->createStub(CommitteeMembership::class);
            $committeeMembership->method('getCommitteeUuid')->willReturn(Uuid::fromString($committeeUuid));
        }

        $memberships = array_map(static function (string $uuid): AgoraMembership {
            $agora = new \ReflectionClass(Agora::class)->newInstanceWithoutConstructor();
            new \ReflectionProperty(Agora::class, 'uuid')->setValue($agora, Uuid::fromString($uuid));

            $membership = new AgoraMembership();
            $membership->agora = $agora;

            return $membership;
        }, $agoraUuids);

        \Closure::bind(function () use ($committeeMembership, $memberships): void {
            $this->committeeMembership = $committeeMembership;
            $this->agoraMemberships = new ArrayCollection($memberships);
        }, $user, Adherent::class)();

        return $user;
    }

    private function hide(string $uuid): void
    {
        $this->manager->persist(new TimelineHiddenFeed(Uuid::fromString($uuid)));
        $this->manager->flush();
    }
}
