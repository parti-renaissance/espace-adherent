<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api\Vox;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\Entity\Timeline\TimelineFeed;
use App\JeMengage\Timeline\Indexer\FeedItem;
use App\JeMengage\Timeline\Indexer\FeedResponse;
use App\JeMengage\Timeline\Indexer\TimelineRankerClient;
use App\JeMengage\Timeline\Indexer\UserProfile;
use App\OAuth\Model\GrantTypeEnum;
use App\OAuth\Model\Scope;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ApiControllerTestTrait;

/**
 * Canary read path of GET /api/v3/je-mengage/timeline_feeds. The current user (president-ad) is the only
 * canaryTester fixture, so the request forks to the ranker: get_items is mocked through the global
 * MockHttpClientCallback (host fixture indexer.timeline.test.json) and the timeline_feed rows are inserted
 * here. Non-canary regression is covered by features/api/timeline_feeds.feature.
 */
#[Group('functional')]
#[Group('api')]
class GetTimelineFeedsCanaryControllerTest extends AbstractApiTestCase
{
    use ApiControllerTestTrait;

    private const string ENDPOINT = '/api/v3/je-mengage/timeline_feeds';
    // Mock ranker host; get_items resolves against the host fixture indexer.timeline.test.json.
    private const string RANKER_URL = 'https://indexer.timeline.test';
    // Mock ranker host whose get_items fixture omits "items" (host fixture ranker-invalid.timeline.test.json).
    private const string RANKER_INVALID_URL = 'https://ranker-invalid.timeline.test';

    // Must match tests/HttpClient/fixtures/indexer.timeline.test.json.
    private const string UUID_A = 'aaaa1111-1111-4111-8111-aaaaaaaaaaaa';
    private const string UUID_MISSING = 'cccc3333-3333-4333-8333-cccccccccccc';
    private const string UUID_B = 'bbbb2222-2222-4222-8222-bbbbbbbbbbbb';
    // Never returned by the ranker fixture: keeps the candidate set non-empty without being served.
    private const string UUID_UNRANKED = 'dddd4444-4444-4444-8444-dddddddddddd';
    // View-filter committee: only seeded rows carry this reach.
    private const string COMMITTEE_FILTER_UUID = 'eeee5555-5555-4555-8555-eeeeeeeeeeee';
    // A publication targeting a tag no fixture user carries: a local row the user cannot see.
    private const array UNAUTHORIZED_AUDIENCE = ['include' => ['tags' => ['clamp:test:none']]];

    protected function setUp(): void
    {
        parent::setUp();

        // One stable kernel for the whole test (token request + inserts + GET share the EM/connection).
        $this->client->disableReboot();
        $this->manager->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->manager->getConnection()->rollBack();
        $_SERVER['TIMELINE_RANKER_URL'] = $_ENV['TIMELINE_RANKER_URL'] = '';

        parent::tearDown();
    }

    public function testCanaryReturnsHydratedFeedInIndexerOrderSkippingOrphans(): void
    {
        $token = $this->canaryToken();
        $this->insertFeed(self::UUID_A, 'Indexer item A');
        $this->insertFeed(self::UUID_B, 'Indexer item B');
        $this->manager->flush();
        $this->enableRanker();

        $payload = $this->requestTimeline($token, 0, 'sess-123');

        // Order is the indexer authority (A, MISSING, B); the orphan (no local row) is skipped -> [A, B].
        self::assertCount(2, $payload['hits']);
        self::assertSame('Indexer item A', $payload['hits'][0]['title']);
        self::assertSame('Indexer item B', $payload['hits'][1]['title']);

        self::assertSame(2, $payload['nbHits']);
        self::assertSame(0, $payload['page']);
        // With a session_id the total is unknown: while the indexer returns items, nbPages claims one more page.
        self::assertSame(2, $payload['nbPages']);
        self::assertSame(10, $payload['hitsPerPage']);
    }

    public function testCanaryWithoutSessionUsesServerFallbackCursorAndPaginates(): void
    {
        $token = $this->canaryToken();
        $this->insertFeed(self::UUID_A, 'Indexer item A');
        $this->insertFeed(self::UUID_B, 'Indexer item B');
        $this->manager->flush();
        $this->enableRanker();

        // No session_id from the app: the controller mints a server-side fallback cursor (TimelineSessionResolver,
        // persisted in cache) so legacy apps still paginate instead of being capped to a single page. nbPages = 2
        // proves a cursor was supplied (pagination mode), not the single-page degrade (which would be nbPages = 1).
        $payload = $this->requestTimeline($token);

        self::assertCount(2, $payload['hits']);
        self::assertSame(0, $payload['page']);
        self::assertSame(2, $payload['nbPages']);
    }

    public function testCanaryWithSessionRelaysSubsequentPagesInsteadOfEmpty(): void
    {
        $token = $this->canaryToken();
        $this->insertFeed(self::UUID_A, 'Indexer item A');
        $this->insertFeed(self::UUID_B, 'Indexer item B');
        $this->manager->flush();
        $this->enableRanker();

        // page 1 relays a fresh get_items call (the indexer owns the cursor) and serves the returned batch
        // instead of an empty envelope. nbPages = page + 2 (the indexer returned items).
        $payload = $this->requestTimeline($token, 1, 'sess-123');

        self::assertCount(2, $payload['hits']);
        self::assertSame('Indexer item A', $payload['hits'][0]['title']);
        self::assertSame('Indexer item B', $payload['hits'][1]['title']);
        self::assertSame(1, $payload['page']);
        self::assertSame(3, $payload['nbPages']);
    }

    public function testRankerIdsOutsideCandidateSetAreClamped(): void
    {
        $token = $this->canaryToken();
        $this->insertFeed(self::UUID_A, 'Indexer item A');
        $this->insertFeed(self::UUID_B, 'Indexer item B');
        // Local row the user is NOT authorized to see; the ranker fixture still returns its uuid.
        // Without the clamp it would be hydrated and served (it exists and is not hidden).
        $this->insertFeed(self::UUID_MISSING, 'Secret item', self::UNAUTHORIZED_AUDIENCE, 'publication');
        $this->manager->flush();
        $this->enableRanker();

        $payload = $this->requestTimeline($token, 0, 'sess-123');

        self::assertCount(2, $payload['hits']);
        self::assertSame('Indexer item A', $payload['hits'][0]['title']);
        self::assertSame('Indexer item B', $payload['hits'][1]['title']);
        // The post-clamp list is non-empty: pagination continues as usual.
        self::assertSame(2, $payload['nbPages']);
    }

    public function testAllClampedBatchEndsPagination(): void
    {
        $token = $this->canaryToken();
        // Every uuid the ranker fixture returns maps to an unauthorized local row...
        $this->insertFeed(self::UUID_A, 'Secret A', self::UNAUTHORIZED_AUDIENCE, 'publication');
        $this->insertFeed(self::UUID_B, 'Secret B', self::UNAUTHORIZED_AUDIENCE, 'publication');
        // ...and one authorized row keeps the candidate set non-empty (no short-circuit).
        $this->insertFeed(self::UUID_UNRANKED, 'Visible but not ranked');
        $this->manager->flush();
        $this->enableRanker();

        $payload = $this->requestTimeline($token, 0, 'sess-123');

        self::assertSame([], $payload['hits']);
        self::assertSame(0, $payload['nbHits']);
        // An all-clamped batch ends the scroll (page + 1): a clamped id is never servable.
        self::assertSame(1, $payload['nbPages']);
    }

    public function testEmptyCandidateSetSkipsRankerCall(): void
    {
        $token = $this->canaryToken();
        // Empty the mirror inside the test transaction: zero candidates, for any audience.
        $this->manager->createQuery('DELETE FROM '.TimelineFeed::class)->execute();
        // An invalid ranker host proves the call is never made: reaching it would throw and fall
        // back to Algolia (hitsPerPage 20); the indexer-shaped envelope (10) is the short-circuit.
        $_SERVER['TIMELINE_RANKER_URL'] = $_ENV['TIMELINE_RANKER_URL'] = self::RANKER_INVALID_URL;

        $payload = $this->requestTimeline($token, 0, 'sess-123');

        self::assertSame([], $payload['hits']);
        self::assertSame(1, $payload['nbPages']);
        self::assertSame(10, $payload['hitsPerPage']);
    }

    public function testRankerRequestCarriesCandidateIds(): void
    {
        $token = $this->canaryToken();
        $this->insertFeed(self::UUID_A, 'Indexer item A');
        $this->insertFeed(self::UUID_B, 'Indexer item B');
        $this->manager->flush();
        $this->enableRanker();

        $rankerClient = $this->createMock(TimelineRankerClient::class);
        $rankerClient
            ->expects(self::once())
            ->method('getItems')
            ->with(
                self::isInstanceOf(UserProfile::class),
                self::callback(static function (array $candidates): bool {
                    return \in_array(self::UUID_A, $candidates, true)
                        && \in_array(self::UUID_B, $candidates, true);
                }),
                'sess-123'
            )
            ->willReturn(new FeedResponse([new FeedItem(self::UUID_A, 'news', 1.0)]));
        static::getContainer()->set(TimelineRankerClient::class, $rankerClient);

        $payload = $this->requestTimeline($token, 0, 'sess-123');

        self::assertCount(1, $payload['hits']);
        self::assertSame('Indexer item A', $payload['hits'][0]['title']);
    }

    public function testFilteredViewRestrictsCandidates(): void
    {
        $token = $this->canaryToken();
        // national = base clause grant; the committee reach is what the view filter matches on.
        $this->insertFeed(self::UUID_A, 'Committee event', ['include' => ['national' => true, 'committees' => [self::COMMITTEE_FILTER_UUID]]], 'event');
        $this->insertFeed(self::UUID_B, 'National news');
        $this->manager->flush();
        $this->enableRanker();

        $rankerClient = $this->createMock(TimelineRankerClient::class);
        $rankerClient
            ->expects(self::once())
            ->method('getItems')
            ->with(
                self::isInstanceOf(UserProfile::class),
                // The filtered view restricts the candidates BEFORE the ranker call.
                self::callback(static function (array $candidates): bool {
                    return \in_array(self::UUID_A, $candidates, true)
                        && !\in_array(self::UUID_B, $candidates, true);
                }),
                'sess-123'
            )
            // The ranker answers with an out-of-view id anyway: the clamp must drop it.
            ->willReturn(new FeedResponse([
                new FeedItem(self::UUID_A, 'event', 1.0),
                new FeedItem(self::UUID_B, 'news', 0.9),
            ]));
        static::getContainer()->set(TimelineRankerClient::class, $rankerClient);

        $payload = $this->requestTimeline($token, 0, 'sess-123', '&committee='.self::COMMITTEE_FILTER_UUID);

        self::assertCount(1, $payload['hits']);
        self::assertSame('Committee event', $payload['hits'][0]['title']);
    }

    public function testFilteredViewWithNoMatchSkipsRanker(): void
    {
        $token = $this->canaryToken();
        $this->insertFeed(self::UUID_A, 'National news');
        $this->manager->flush();
        // No row carries this committee reach: zero candidates for the filtered view. The invalid
        // ranker host proves the call is never made (reaching it would fall back to Algolia, 20).
        $_SERVER['TIMELINE_RANKER_URL'] = $_ENV['TIMELINE_RANKER_URL'] = self::RANKER_INVALID_URL;

        $payload = $this->requestTimeline($token, 0, 'sess-123', '&committee='.self::COMMITTEE_FILTER_UUID);

        self::assertSame([], $payload['hits']);
        self::assertSame(1, $payload['nbPages']);
        self::assertSame(10, $payload['hitsPerPage']);
    }

    public function testCanaryFallsBackToAlgoliaWhenRankerFails(): void
    {
        $token = $this->canaryToken();
        // The ranker is configured but answers with a payload missing "items": getItems throws a
        // RuntimeException. A canary must never break the feed, so the controller silently falls back to
        // the regular Algolia path instead of returning a 503. (An empty TIMELINE_RANKER_URL is a config
        // error handled upstream — the scoped client fails to build, a 500 — so it is not exercised here.)
        $_SERVER['TIMELINE_RANKER_URL'] = $_ENV['TIMELINE_RANKER_URL'] = self::RANKER_INVALID_URL;

        $this->client->request(Request::METHOD_GET, self::ENDPOINT, [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $token",
        ]);

        $response = $this->client->getResponse();
        self::assertSame(Response::HTTP_OK, $response->getStatusCode(), $response->getContent());

        $payload = json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        // Algolia-envelope shape proves the fallback ran: hitsPerPage is 20 there, vs 10 on the indexer path.
        self::assertArrayHasKey('hits', $payload);
        self::assertArrayHasKey('nbHits', $payload);
        self::assertArrayHasKey('page', $payload);
        self::assertArrayHasKey('nbPages', $payload);
        self::assertSame(20, $payload['hitsPerPage']);
    }

    private function canaryToken(): string
    {
        return $this->getAccessToken(
            LoadClientData::CLIENT_10_UUID,
            'MWFod6bOZb2mY3wLE=4THZGbOfHJvRHk8bHdtZP3BTr',
            GrantTypeEnum::PASSWORD,
            Scope::JEMARCHE_APP,
            'president-ad@renaissance-dev.fr',
            LoadAdherentData::DEFAULT_PASSWORD,
        );
    }

    private function requestTimeline(string $token, int $page = 0, ?string $sessionId = null, string $extraQuery = ''): array
    {
        $uri = self::ENDPOINT.'?page='.$page.$extraQuery;
        if (null !== $sessionId) {
            $uri .= '&session_id='.$sessionId;
        }

        $this->client->request(Request::METHOD_GET, $uri, [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $token",
        ]);

        $response = $this->client->getResponse();
        self::assertSame(Response::HTTP_OK, $response->getStatusCode(), $response->getContent());

        return json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
    }

    private function enableRanker(): void
    {
        $_SERVER['TIMELINE_RANKER_URL'] = $_ENV['TIMELINE_RANKER_URL'] = self::RANKER_URL;
    }

    /**
     * National by default: under the local authorization boundary a row without any reach grant is
     * not a candidate anymore (Algolia base clause parity), so the rows the ranker is expected to
     * serve must be visible to the test user.
     */
    private function insertFeed(string $uuid, string $title, ?array $audience = ['include' => ['national' => true]], string $type = 'news'): void
    {
        $feed = new TimelineFeed();
        new \ReflectionProperty(TimelineFeed::class, 'uuid')->setValue($feed, Uuid::fromString($uuid));
        $feed->type = $type;
        $feed->publicationDate = new \DateTimeImmutable('2026-05-20 10:00:00');
        $feed->audience = $audience;
        $feed->display = ['objectID' => $uuid, 'type' => $type, 'title' => $title];
        $feed->updatedAt = new \DateTimeImmutable('2026-05-20 10:00:00');

        $this->manager->persist($feed);
    }
}
