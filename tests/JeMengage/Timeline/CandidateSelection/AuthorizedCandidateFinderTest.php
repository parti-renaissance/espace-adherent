<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\CandidateSelection;

use App\Entity\Timeline\TimelineFeed;
use App\Entity\Timeline\TimelineHiddenFeed;
use App\JeMengage\Timeline\CandidateSelection\AudienceContext;
use App\JeMengage\Timeline\CandidateSelection\AudienceMatcher;
use App\JeMengage\Timeline\CandidateSelection\AuthorizedCandidateFinder;
use App\JeMengage\Timeline\CandidateSelection\RequestFilterCondition;
use App\JeMengage\Timeline\CandidateSelection\TimelineRequestFilter;
use App\JeMengage\Timeline\Indexer\UserProfile;
use App\Repository\Timeline\TimelineFeedRepository;
use PHPUnit\Framework\Attributes\Group;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Uid\Uuid;
use Tests\App\AbstractKernelTestCase;

/**
 * Golden-fixtures test of the candidate computation (DESIGN § Impact/Tests): a representative item
 * set crossed with contrasted user profiles, expected visibility derived BY HAND from the Algolia
 * clauses. Real wiring: actual DB rows through the real repository + matcher; the contexts are
 * hand-built for a deterministic matrix (AudienceContextFactory has its own functional coverage).
 *
 * The test DB carries fixture timeline_feed rows (dated around "now"): the seeded rows are dated in
 * 2027 to outrank them in the newest-first scan, and the matrix asserts the ORDERED INTERSECTION of
 * the result with the seeded uuids, so matching fixture rows never break the expectations.
 */
#[Group('functional')]
final class AuthorizedCandidateFinderTest extends AbstractKernelTestCase
{
    private const string NAT = 'aaaa0001-0000-4000-8000-000000000001';
    private const string CITY_EVT = 'aaaa0002-0000-4000-8000-000000000002';
    private const string AGORA_EVT = 'aaaa0003-0000-4000-8000-000000000003';
    private const string CMT_EVT = 'aaaa0004-0000-4000-8000-000000000004';
    private const string PUB_OPEN = 'aaaa0005-0000-4000-8000-000000000005';
    private const string PUB_TAG = 'aaaa0006-0000-4000-8000-000000000006';
    private const string PUB_EXCL = 'aaaa0007-0000-4000-8000-000000000007';
    private const string PUB_ZONE = 'aaaa0008-0000-4000-8000-000000000008';
    private const string PUB_AGE = 'aaaa0009-0000-4000-8000-000000000009';
    private const string TRANS = 'aaaa0010-0000-4000-8000-000000000010';
    private const string HIDDEN = 'aaaa0011-0000-4000-8000-000000000011';
    private const string OLD_NAT = 'aaaa0012-0000-4000-8000-000000000012';

    private const array SEEDED = [
        self::NAT, self::CITY_EVT, self::AGORA_EVT, self::CMT_EVT, self::PUB_OPEN, self::PUB_TAG,
        self::PUB_EXCL, self::PUB_ZONE, self::PUB_AGE, self::TRANS, self::HIDDEN, self::OLD_NAT,
    ];

    private ?TimelineFeedRepository $repository = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = static::getContainer()->get(TimelineFeedRepository::class);

        $this->manager->getConnection()->beginTransaction();

        // Newest first: 2027-07-20 down to 2027-07-09, then the "old" national item.
        $this->seed(self::NAT, 'news', '2027-07-20 10:00:00', ['include' => ['national' => true]]);
        $this->seed(self::CITY_EVT, 'event', '2027-07-19 10:00:00', ['include' => ['zones' => ['city:75056']]]);
        $this->seed(self::AGORA_EVT, 'event', '2027-07-18 10:00:00', ['include' => ['agoras' => ['agora-1']]]);
        // national:true is the base clause grant (committee reach is NOT one); committees feeds the
        // view-filter test. Mirrors reality: committee events also carry zone/national reach.
        $this->seed(self::CMT_EVT, 'event', '2027-07-17 10:00:00', ['include' => ['national' => true, 'committees' => ['cmt-evt']]]);
        $this->seed(self::PUB_OPEN, 'publication', '2027-07-16 10:00:00', null);
        $this->seed(self::PUB_TAG, 'publication', '2027-07-15 10:00:00', ['include' => ['tags' => ['t:x']]]);
        $this->seed(self::PUB_EXCL, 'publication', '2027-07-14 10:00:00', ['exclude' => ['tags' => ['t']]]);
        $this->seed(self::PUB_ZONE, 'publication', '2027-07-13 10:00:00', ['include' => ['zones' => ['department:75']]]);
        $this->seed(self::PUB_AGE, 'publication', '2027-07-12 10:00:00', ['include' => ['age_min' => 30]]);
        $this->seed(self::TRANS, 'transactional_message', '2027-07-11 10:00:00', ['include' => ['adherent_ids' => [9901]]]);
        $this->seed(self::HIDDEN, 'news', '2027-07-10 10:00:00', ['include' => ['national' => true]]);
        $this->manager->persist(new TimelineHiddenFeed(Uuid::fromString(self::HIDDEN)));
        $this->seed(self::OLD_NAT, 'news', '2026-12-01 10:00:00', ['include' => ['national' => true]]);
        $this->manager->flush();
    }

    protected function tearDown(): void
    {
        $this->manager->getConnection()->rollBack();
        $this->repository = null;

        parent::tearDown();
    }

    public function testFindCandidateUuidsMatchesGoldenMatrix(): void
    {
        $finder = $this->finder();

        // Paris member, 30y, tag "t:x": everything aimed at her EXCEPT the agora-only event (agora
        // reach is not a base clause grant), the tag-excluded publication, the private message
        // (type excluded) and the hidden row.
        self::assertSame(
            [self::NAT, self::CITY_EVT, self::CMT_EVT, self::PUB_OPEN, self::PUB_TAG, self::PUB_ZONE, self::PUB_AGE, self::OLD_NAT],
            $this->seededSubset($finder->findCandidateUuids(self::parisContext()))
        );

        // Out-of-zone user, no tags, no age: national + open content only; the age-bounded
        // publication is fail-closed on the null age.
        self::assertSame(
            [self::NAT, self::CMT_EVT, self::PUB_OPEN, self::PUB_EXCL, self::OLD_NAT],
            $this->seededSubset($finder->findCandidateUuids(self::outsideContext()))
        );

        // 25y in dept 75, member of agora-1: the zone-targeted publication passes, the age-bounded
        // one does not, and the agora-only event stays INVISIBLE despite the membership (Algolia
        // base clause parity — assumed divergence, DESIGN Decision 2).
        self::assertSame(
            [self::NAT, self::CMT_EVT, self::PUB_OPEN, self::PUB_EXCL, self::PUB_ZONE, self::OLD_NAT],
            $this->seededSubset($finder->findCandidateUuids(self::young75Context()))
        );
    }

    public function testFindCandidateUuidsStopsAtCandidateCapAndLogs(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info')->with(self::stringContains('candidate cap'), self::anything());

        $candidates = $this->finder(logger: $logger, maxCandidates: 2)->findCandidateUuids(self::parisContext());

        // The 2027-dated seeds are the newest rows of the whole table: the first 2 matches are exact.
        self::assertSame([self::NAT, self::CITY_EVT], $candidates);
    }

    public function testFindCandidateUuidsStopsAtScanCapAndWarns(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('warning')->with(self::stringContains('scan cap'), self::anything());

        // Only the 3 newest rows (NAT, CITY_EVT, AGORA_EVT) are scanned: the open publication
        // (4th newest) is authorized but never reached — the documented sparse-audience truncation.
        $candidates = $this->finder(logger: $logger, chunkSize: 2, maxScannedRows: 3)->findCandidateUuids(self::parisContext());

        self::assertSame([self::NAT, self::CITY_EVT], $candidates);
        self::assertNotContains(self::PUB_OPEN, $candidates);
    }

    public function testFindCandidateUuidsAppliesViewFilter(): void
    {
        $filter = new TimelineRequestFilter([new RequestFilterCondition(RequestFilterCondition::COMMITTEE, 'cmt-evt')]);

        $candidates = $this->finder()->findCandidateUuids(self::parisContext(), $filter);

        // Strict equality on the FULL result: no fixture row carries this committee reach, and the
        // national news/publications are excluded by the committee condition (publications by the
        // type guard) — the filtered view serves the committee event only.
        self::assertSame([self::CMT_EVT], $candidates);
    }

    private function finder(?LoggerInterface $logger = null, int $maxCandidates = 2000, int $chunkSize = 500, int $maxScannedRows = 10000): AuthorizedCandidateFinder
    {
        // Built by hand: the finder has no container referent until the provider consumes it (phase 7).
        return new AuthorizedCandidateFinder(
            $this->repository,
            new AudienceMatcher(new NullLogger()),
            $logger ?? new NullLogger(),
            $maxCandidates,
            $chunkSize,
            $maxScannedRows,
        );
    }

    /**
     * Ordered intersection with the seeded uuids: matching fixture rows are ignored, the relative
     * (newest-first) order of the seeded rows is preserved and asserted.
     *
     * @param string[] $candidates
     *
     * @return string[]
     */
    private function seededSubset(array $candidates): array
    {
        return array_values(array_intersect($candidates, self::SEEDED));
    }

    private function seed(string $uuid, string $type, string $publicationDate, ?array $audience): void
    {
        $feed = new TimelineFeed();
        new \ReflectionProperty(TimelineFeed::class, 'uuid')->setValue($feed, Uuid::fromString($uuid));
        $feed->type = $type;
        $feed->publicationDate = new \DateTimeImmutable($publicationDate);
        $feed->audience = $audience;
        $feed->display = ['objectID' => $uuid, 'type' => $type, 'title' => 'Item '.$uuid];
        $feed->updatedAt = new \DateTimeImmutable($publicationDate);

        $this->manager->persist($feed);
    }

    private static function parisContext(): AudienceContext
    {
        return new AudienceContext(
            new UserProfile(9901, ['t:x'], ['city:75056', 'department:75', 'region:11'], ['cmt-1111'], [], [], [], 1, [], 'female', 30),
            ['t', 't:x'],
            ['department:75', 'city:75056'],
            self::zonesByType(city: ['75056'], department: ['75'], region: ['11']),
        );
    }

    private static function outsideContext(): AudienceContext
    {
        return new AudienceContext(
            new UserProfile(9902, [], ['city:13001', 'department:13'], [], [], [], [], 0, []),
            [],
            ['department:13', 'city:13001'],
            self::zonesByType(city: ['13001'], department: ['13']),
        );
    }

    private static function young75Context(): AudienceContext
    {
        return new AudienceContext(
            new UserProfile(9903, [], ['department:75'], [], ['agora-1'], [], [], 0, [], null, 25),
            [],
            ['department:75'],
            self::zonesByType(department: ['75']),
        );
    }

    private static function zonesByType(array $city = [], array $department = [], array $region = []): array
    {
        return [
            'borough' => [],
            'canton' => [],
            'city' => $city,
            'department' => $department,
            'region' => $region,
            'country' => [],
            'district' => [],
            'foreign_district' => [],
            'custom' => [],
        ];
    }
}
