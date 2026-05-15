<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\DataFixtures\ORM\LoadEventData;
use App\OAuth\Model\GrantTypeEnum;
use App\OAuth\Model\Scope;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('api')]
class HubItemControllerTest extends AbstractApiTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    private const URL = '/api/v3/hub-item';
    private const PRESIDENT_AD_UUID = '9fec3385-8cfb-46e8-8305-c9bae10e4517';

    private ?string $accessToken = null;

    /**
     * Regression: `ActionExtension` does `leftJoin participants` + `addSelect('participant')`, which
     * multiplies SQL rows by the number of participants per Action. Without a proper Paginator,
     * `setMaxResults($limit)` bounds the raw rows instead of distinct entities, so Actions get
     * lost silently when (actions × participants) exceeds the fetch limit.
     *
     * Each fixture Action has ~11 participants. With $fetchLimit=300, raw rows ≈ 53 × 11 = 583 — well
     * above the limit. This test asserts every distinct Action surfaces in the page anyway.
     */
    public function testHubFetcherReturnsDistinctActionsRegardlessOfParticipantsJoinFanOut(): void
    {
        $data = $this->call(self::URL.'?page_size=300');

        $actions = array_filter($data['items'], static fn ($i) => 'action' === $i['type']);
        $uniqueUuids = array_unique(array_column($actions, 'uuid'));

        self::assertCount(\count($uniqueUuids), $actions, 'No duplicate Action UUID expected in the page');
        self::assertGreaterThanOrEqual(
            50,
            \count($uniqueUuids),
            \sprintf(
                'Expected at least 50 distinct Actions (fixtures load 53). Got %d — likely a raw SQL row-cap leak (see Doctrine Paginator usage in AbstractHubFetcher::fetch).',
                \count($uniqueUuids)
            )
        );
    }

    public function testHubItemBaselineMergesEventsAndActions(): void
    {
        $data = $this->call(self::URL.'?page_size=300');

        self::assertGreaterThan(0, $data['metadata']['total_items']);

        $types = array_count_values(array_column($data['items'], 'type'));
        self::assertArrayHasKey('event', $types, 'Baseline must contain at least one event');
        self::assertArrayHasKey('action', $types, 'Baseline must contain at least one action');
    }

    public function testCompatibleFilterZoneRestrictsResults(): void
    {
        $baseline = $this->call(self::URL.'?page_size=300')['metadata']['total_items'];
        $filtered = $this->call(self::URL.'?page_size=300&zone=92')['metadata']['total_items'];

        self::assertLessThanOrEqual($baseline, $filtered);
        self::assertGreaterThan(0, $filtered, 'zone=92 should still return data');
    }

    public function testCompatibleFilterBboxRestrictsResults(): void
    {
        $baseline = $this->call(self::URL.'?page_size=300')['metadata']['total_items'];
        $tiny = $this->call(self::URL.'?page_size=300&bbox[ne][lat]=1&bbox[ne][lng]=1&bbox[sw][lat]=0&bbox[sw][lng]=0')['metadata']['total_items'];

        self::assertLessThan($baseline, $tiny, 'A tiny bbox far from data must drop items vs baseline');
    }

    public function testCompatibleFilterBeginAtStrictlyAfterReturnsEmptyForFarFuture(): void
    {
        $baseline = $this->call(self::URL.'?page_size=300')['metadata']['total_items'];
        $future = new \DateTimeImmutable('+100 years')->format(\DATE_ATOM);
        $filtered = $this->call(self::URL.'?page_size=300&beginAt[strictly_after]='.urlencode($future))['metadata']['total_items'];

        self::assertSame(0, $filtered, 'beginAt[strictly_after] in the far future must return 0 items');
        self::assertGreaterThan(0, $baseline);
    }

    public function testBeginAtStrictlyAfterDoesNotLeakPastItems(): void
    {
        $threshold = new \DateTimeImmutable('+10 minutes');
        $data = $this->call(self::URL.'?page_size=300&beginAt[strictly_after]='.urlencode($threshold->format(\DATE_ATOM)));

        $leaks = [];
        foreach ($data['items'] as $item) {
            $beginAt = $item['begin_at'] ?? null;
            if (null === $beginAt) {
                continue;
            }
            if (new \DateTimeImmutable($beginAt) <= $threshold) {
                $leaks[] = \sprintf('%s %s @ %s (name=%s)', $item['type'], $item['uuid'], $beginAt, $item['name'] ?? '');
            }
        }

        self::assertSame([], $leaks, "No past item should leak when filtering beginAt[strictly_after]. Leaks:\n".implode("\n", $leaks));
    }

    public function testBeginAtStrictlyBeforeDoesNotLeakFutureItems(): void
    {
        $threshold = new \DateTimeImmutable('-1 minute');
        $data = $this->call(self::URL.'?page_size=300&beginAt[strictly_before]='.urlencode($threshold->format(\DATE_ATOM)));

        $leaks = [];
        foreach ($data['items'] as $item) {
            $beginAt = $item['begin_at'] ?? null;
            if (null === $beginAt) {
                continue;
            }
            if (new \DateTimeImmutable($beginAt) >= $threshold) {
                $leaks[] = \sprintf('%s %s @ %s (name=%s)', $item['type'], $item['uuid'], $beginAt, $item['name'] ?? '');
            }
        }

        self::assertSame([], $leaks, "No future item should leak when filtering beginAt[strictly_before]. Leaks:\n".implode("\n", $leaks));
    }

    public function testPastActionsAreReachableNowThatNowMinusOneHourGateIsRemoved(): void
    {
        $threshold = new \DateTimeImmutable('-1 hour');
        $data = $this->call(self::URL.'?page_size=300&beginAt[strictly_before]='.urlencode($threshold->format(\DATE_ATOM)));

        $actions = array_filter($data['items'], static fn ($i) => 'action' === $i['type']);

        self::assertGreaterThan(
            0,
            \count($actions),
            'Past actions must be reachable via beginAt[strictly_before] now that ActionExtension no longer enforces date >= NOW-1h by default'
        );

        foreach ($actions as $action) {
            self::assertLessThan(
                $threshold,
                new \DateTimeImmutable($action['begin_at']),
                'All returned actions must be in the past'
            );
        }
    }

    public function testBeginAtSearchFilterStrategyStartMatchesByPrefix(): void
    {
        $thisMonth = new \DateTimeImmutable()->format('Y-m');
        $data = $this->call(self::URL.'?page_size=300&beginAt='.urlencode($thisMonth));

        self::assertGreaterThan(0, $data['metadata']['total_items']);

        foreach ($data['items'] as $item) {
            self::assertStringStartsWith(
                $thisMonth,
                $item['begin_at'],
                'beginAt=YYYY-MM (start strategy) should only return items whose begin_at starts with this prefix'
            );
        }
    }

    /**
     * `status` is not declared on the HubItemView resource but EventExtension::applyToCollection
     * reads $filters['status'] directly — so it transparently works for the event slice.
     */
    public function testUpcomingOnlyFiltersBothEventAndActionSlices(): void
    {
        $now = new \DateTimeImmutable();
        $data = $this->call(self::URL.'?page_size=300&upcomingOnly=1');

        self::assertGreaterThan(0, $data['metadata']['total_items']);

        foreach ($data['items'] as $item) {
            if ('event' === $item['type']) {
                self::assertNotNull($item['finish_at']);
                self::assertGreaterThanOrEqual(
                    $now,
                    new \DateTimeImmutable($item['finish_at']),
                    'Event must have finish_at >= NOW'
                );

                continue;
            }

            self::assertGreaterThanOrEqual(
                $now,
                new \DateTimeImmutable($item['begin_at']),
                'Action must have begin_at >= NOW (Action.finishAt aliased on date)'
            );
        }
    }

    public function testStatusOverrideIsIgnoredOnHubItemFeed(): void
    {
        $baseline = $this->call(self::URL.'?page_size=300')['metadata']['total_items'];
        $cancelledAttempt = $this->call(self::URL.'?page_size=300&status=CANCELLED')['metadata']['total_items'];

        self::assertSame(
            $baseline,
            $cancelledAttempt,
            'status=CANCELLED must be ignored on /v3/hub-item — cancelled items must never surface in the public feed'
        );
    }

    public function testFinishAtStrictlyAfterFiltersEventsByFinishAtAndActionsByDate(): void
    {
        $thresholdDt = new \DateTimeImmutable('+1 hour');
        $threshold = $thresholdDt->format(\DATE_ATOM);
        $data = $this->call(self::URL.'?page_size=300&finishAt[strictly_after]='.urlencode($threshold));

        foreach ($data['items'] as $item) {
            if ('event' === $item['type']) {
                self::assertNotNull($item['finish_at']);
                self::assertGreaterThan(
                    $thresholdDt,
                    new \DateTimeImmutable($item['finish_at']),
                    'Returned event must have finish_at > threshold'
                );

                continue;
            }

            // Action: finishAt is aliased on Action.date (Action has no finish_at column).
            self::assertGreaterThan(
                $thresholdDt,
                new \DateTimeImmutable($item['begin_at']),
                'Returned action must have begin_at > threshold (finishAt aliased on Action.date)'
            );
        }
    }

    public function testOnlyMineReturnsOnlyOwnedItems(): void
    {
        $data = $this->call(self::URL.'?page_size=300&only_mine=1');

        self::assertGreaterThan(0, $data['metadata']['total_items']);

        $currentUserUuid = self::PRESIDENT_AD_UUID;

        foreach ($data['items'] as $item) {
            self::assertSame(
                $currentUserUuid,
                $item['organizer']['uuid'] ?? null,
                \sprintf('Item %s should be authored by the current user, got organizer %s', $item['uuid'], json_encode($item['organizer'] ?? null))
            );
        }
    }

    public function testSubscribedOnlyReturnsRegistrationsIncludingHiddenAndPastEvents(): void
    {
        $data = $this->call(self::URL.'?page_size=300&subscribedOnly=1');

        self::assertGreaterThan(0, $data['metadata']['total_items']);

        $eventUuids = [];
        foreach ($data['items'] as $item) {
            if ('event' === $item['type']) {
                $eventUuids[] = $item['uuid'];
            }
        }

        self::assertContains(
            LoadEventData::EVENT_8_UUID,
            $eventUuids,
            'subscribedOnly=1 must surface past INVITATION events the user is registered to (event-8, -2 months)'
        );
    }

    public function testSubscribedOnlyDoesNotLeakUnrelatedItems(): void
    {
        $baseline = $this->call(self::URL.'?page_size=300')['metadata']['total_items'];
        $subscribed = $this->call(self::URL.'?page_size=300&subscribedOnly=1')['metadata']['total_items'];

        self::assertLessThanOrEqual(
            $baseline,
            $subscribed,
            'subscribedOnly must not exceed the baseline (it joins on EventRegistration and ActionParticipant)'
        );
    }

    public function testOrderBeginAtAscOrdersItemsByBeginAtAscending(): void
    {
        $data = $this->call(self::URL.'?page_size=300&order[beginAt]=asc');

        $previous = null;
        foreach ($data['items'] as $item) {
            $current = new \DateTimeImmutable($item['begin_at']);

            if (null !== $previous) {
                self::assertGreaterThanOrEqual($previous, $current, 'order[beginAt]=asc must order items chronologically');
            }
            $previous = $current;
        }
    }

    public function testOrderBeginAtDescOrdersItemsByBeginAtDescending(): void
    {
        $data = $this->call(self::URL.'?page_size=300&order[beginAt]=desc');

        $previous = null;
        foreach ($data['items'] as $item) {
            $current = new \DateTimeImmutable($item['begin_at']);

            if (null !== $previous) {
                self::assertLessThanOrEqual($previous, $current, 'order[beginAt]=desc must order items reverse-chronologically');
            }
            $previous = $current;
        }
    }

    public function testOrderSubscriptionsDescOrdersItemsByParticipantsCount(): void
    {
        $data = $this->call(self::URL.'?page_size=300&order[subscriptions]=desc');

        $previous = null;
        foreach ($data['items'] as $item) {
            $current = (int) ($item['participants_count'] ?? 0);

            if (null !== $previous) {
                self::assertLessThanOrEqual($previous, $current, 'order[subscriptions]=desc must order by participants_count');
            }
            $previous = $current;
        }
    }

    #[DataProvider('provideIgnoredFilters')]
    public function testIgnoredFiltersDoNotAffectResults(string $queryString, string $why): void
    {
        $baseline = $this->call(self::URL.'?page_size=300')['metadata']['total_items'];
        $withFilter = $this->call(self::URL.'?page_size=300&'.$queryString)['metadata']['total_items'];

        self::assertSame(
            $baseline,
            $withFilter,
            "Filter `$queryString` is silently ignored by /v3/hub-item — same count as baseline expected ($why)"
        );
    }

    public static function provideIgnoredFilters(): iterable
    {
        yield 'name (SearchFilter partial)' => ['name=zzzzz-nope', 'SearchFilter on Event not wired in HubItemProvider'];
        yield 'mode (SearchFilter exact)' => ['mode=meeting', 'SearchFilter on Event not wired in HubItemProvider'];
        yield 'pinned (BooleanFilter)' => ['pinned=true', 'BooleanFilter on Event not wired in HubItemProvider'];
    }

    private function call(string $url): array
    {
        $this->client->request(Request::METHOD_GET, $url, [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$this->accessToken]);

        $response = $this->client->getResponse();
        self::assertTrue($response->isSuccessful(), 'Request failed: '.$response->getContent());

        return json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_13_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ15',
            GrantTypeEnum::PASSWORD,
            Scope::JEMARCHE_APP,
            'president-ad@renaissance-dev.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );
    }
}
