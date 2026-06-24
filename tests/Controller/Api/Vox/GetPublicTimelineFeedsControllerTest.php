<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api\Vox;

use App\Entity\Timeline\TimelineFeed;
use App\Entity\Timeline\TimelineHiddenFeed;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;
use Tests\App\AbstractApiTestCase;

#[Group('functional')]
#[Group('api')]
final class GetPublicTimelineFeedsControllerTest extends AbstractApiTestCase
{
    private const string ENDPOINT = '/api/timeline-feeds';

    protected function setUp(): void
    {
        parent::setUp();

        $this->makeApiClient();
        // One stable kernel for the whole test (inserts + GET share the EM/connection).
        $this->client->disableReboot();
        $this->manager->getConnection()->beginTransaction();
        $this->manager->getConnection()->executeStatement('DELETE FROM timeline_feed');
    }

    protected function tearDown(): void
    {
        $this->manager->getConnection()->rollBack();

        parent::tearDown();
    }

    public function testAnonymousRequestReturnsOnlyPublicItemsWithCacheHeaders(): void
    {
        $this->insertFeed('social', 'social_network_post', '2026-05-23 10:00:00');
        $this->insertFeed('action', 'action', '2026-05-22 10:00:00');
        $this->insertFeed('event-public', 'event', '2026-05-21 10:00:00', visibility: 'public');
        $this->insertFeed('event-committee', 'event', '2026-05-24 10:00:00', visibility: 'public', committeeUuid: Uuid::v4()->toRfc4122());
        $this->insertFeed('event-agora', 'event', '2026-05-24 10:00:00', visibility: 'public', agoraUuid: Uuid::v4()->toRfc4122());
        $this->insertFeed('event-adherent', 'event', '2026-05-24 10:00:00', visibility: 'adherent');
        $hidden = $this->insertFeed('event-hidden', 'event', '2026-05-25 10:00:00', visibility: 'public');
        $this->manager->persist(new TimelineHiddenFeed(Uuid::fromString($hidden)));
        $this->manager->flush();

        $this->client->request(Request::METHOD_GET, self::ENDPOINT);

        $response = $this->client->getResponse();
        // Anonymous and not redirected to login: the PUBLIC_ACCESS rule applies.
        self::assertSame(Response::HTTP_OK, $response->getStatusCode(), $response->getContent());

        $payload = json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        self::assertSame(['social', 'action', 'event-public'], array_column($payload['hits'], 'identifier'));
        self::assertSame(3, $payload['nbHits']);
        self::assertSame(10, $payload['hitsPerPage']);

        // No internal/targeting field leaks to the public payload.
        foreach ($payload['hits'] as $hit) {
            foreach (['adherent_ids', 'zone_codes', 'audience', 'access', 'committee_uuid', 'agora_uuid', 'visibility'] as $internal) {
                self::assertArrayNotHasKey($internal, $hit);
            }
        }

        // Short, shared HTTP cache (every visitor sees the same feed).
        self::assertTrue($response->headers->hasCacheControlDirective('public'));
        self::assertSame(120, $response->getMaxAge());
    }

    public function testEmptyMirrorReturnsAnEmptyEnvelope(): void
    {
        $this->client->request(Request::METHOD_GET, self::ENDPOINT);

        $response = $this->client->getResponse();
        self::assertSame(Response::HTTP_OK, $response->getStatusCode(), $response->getContent());

        $payload = json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertSame([], $payload['hits']);
        self::assertSame(0, $payload['nbHits']);
        self::assertSame(0, $payload['nbPages']);
    }

    private function insertFeed(
        string $identifier,
        string $type,
        string $date,
        ?string $visibility = null,
        ?string $committeeUuid = null,
        ?string $agoraUuid = null,
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
            'adherent_ids' => [1, 2],
            'access' => ['author_id' => 1],
        ];
        $feed->updatedAt = new \DateTimeImmutable($date);

        $this->manager->persist($feed);

        return $feed->getUuid()->toRfc4122();
    }
}
