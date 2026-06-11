<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline;

use Algolia\SearchBundle\SearchService;
use App\Entity\Adherent;
use App\Entity\Algolia\AlgoliaJeMengageTimelineFeed;
use App\Entity\Timeline\TimelineHiddenFeed;
use App\JeMengage\Timeline\DataProvider;
use App\JeMengage\Timeline\FeedProcessorPipeline;
use App\Repository\Timeline\TimelineHiddenFeedRepository;
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

        $search = $this->createMock(SearchService::class);
        $search
            ->expects(self::once())
            ->method('rawSearch')
            ->with(AlgoliaJeMengageTimelineFeed::class, '', self::anything())
            ->willReturn([
                'hits' => [
                    ['objectID' => $allowed, 'type' => 'event'],
                    ['objectID' => $hidden, 'type' => 'event'],
                ],
                'nbHits' => 2,
            ])
        ;

        $provider = new DataProvider(
            $search,
            new FeedProcessorPipeline([]),
            static::getContainer()->get(TimelineHiddenFeedRepository::class),
        );

        $result = $provider->findItems($this->createMock(Adherent::class), 0, [], []);

        self::assertSame([$allowed], array_column($result['hits'], 'objectID'));
    }

    private function hide(string $uuid): void
    {
        $this->manager->persist(new TimelineHiddenFeed(Uuid::fromString($uuid)));
        $this->manager->flush();
    }
}
