<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api\Vox;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\Entity\Timeline\TimelineFeed;
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

        $payload = $this->requestTimeline($token);

        // Order is the indexer authority (A, MISSING, B); the orphan (no local row) is skipped -> [A, B].
        self::assertCount(2, $payload['hits']);
        self::assertSame('Indexer item A', $payload['hits'][0]['title']);
        self::assertSame('Indexer item B', $payload['hits'][1]['title']);

        self::assertSame(2, $payload['nbHits']);
        self::assertSame(0, $payload['page']);
        self::assertSame(1, $payload['nbPages']);
        self::assertSame(10, $payload['hitsPerPage']);
    }

    public function testCanaryPageBeyondFirstReturnsEmptyEnvelope(): void
    {
        $token = $this->canaryToken();
        $this->insertFeed(self::UUID_A, 'Indexer item A');
        $this->manager->flush();
        $this->enableRanker();

        $payload = $this->requestTimeline($token, 1);

        self::assertSame([], $payload['hits']);
        self::assertSame(0, $payload['nbHits']);
        self::assertSame(1, $payload['page']);
        self::assertSame(1, $payload['nbPages']);
        self::assertSame(10, $payload['hitsPerPage']);
    }

    public function testCanaryReturns503WhenRankerReturnsUnusablePayload(): void
    {
        $token = $this->canaryToken();
        // The ranker is configured but answers with a payload missing "items": getItems throws a
        // RuntimeException, which the controller maps to a 503. (An empty TIMELINE_RANKER_URL is a config
        // error handled upstream — the scoped client fails to build, a 500 — so it is not exercised here.)
        $_SERVER['TIMELINE_RANKER_URL'] = $_ENV['TIMELINE_RANKER_URL'] = self::RANKER_INVALID_URL;

        $this->client->request(Request::METHOD_GET, self::ENDPOINT, [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $token",
        ]);

        self::assertSame(Response::HTTP_SERVICE_UNAVAILABLE, $this->client->getResponse()->getStatusCode());
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

    private function requestTimeline(string $token, int $page = 0): array
    {
        $this->client->request(Request::METHOD_GET, self::ENDPOINT.'?page='.$page, [], [], [
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

    private function insertFeed(string $uuid, string $title): void
    {
        $feed = new TimelineFeed();
        new \ReflectionProperty(TimelineFeed::class, 'uuid')->setValue($feed, Uuid::fromString($uuid));
        $feed->type = 'news';
        $feed->publicationDate = new \DateTimeImmutable('2026-05-20 10:00:00');
        $feed->display = ['objectID' => $uuid, 'type' => 'news', 'title' => $title];
        $feed->updatedAt = new \DateTimeImmutable('2026-05-20 10:00:00');

        $this->manager->persist($feed);
    }
}
