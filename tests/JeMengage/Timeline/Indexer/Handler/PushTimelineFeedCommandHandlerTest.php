<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\Indexer\Handler;

use App\JeMengage\Timeline\Indexer\Handler\PushTimelineFeedCommandHandler;
use App\JeMengage\Timeline\Indexer\IndexerClient;
use App\JeMengage\Timeline\Indexer\IndexerPayloadFactory;
use App\JeMengage\Timeline\Indexer\Message\PushTimelineFeedCommand;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Uid\Uuid;
use Tests\App\AbstractKernelTestCase;

/**
 * Functional: a real timeline_feed row is read back through the ORM, projected by the real factory,
 * and the resulting POST is captured by an injected recording HttpClient.
 */
class PushTimelineFeedCommandHandlerTest extends AbstractKernelTestCase
{
    private ?Connection $connection = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->manager->getConnection();
        $this->connection->executeStatement('DELETE FROM timeline_feed');
    }

    protected function tearDown(): void
    {
        $this->connection = null;

        parent::tearDown();
    }

    public function testPushesPushableRowToIndexer(): void
    {
        $uuid = Uuid::v4();
        $this->insertRow($uuid, 'event', ['include' => ['zones' => ['department:75']]]);

        $requests = [];
        ($this->handler($requests))(new PushTimelineFeedCommand($uuid));

        self::assertCount(1, $requests);
        self::assertSame('POST', $requests[0]['method']);
        self::assertStringEndsWith('/index', $requests[0]['url']);
        $body = json_decode($requests[0]['options']['body'], true);
        self::assertSame($uuid->toRfc4122(), $body['external_id']);
        self::assertSame('event', $body['kind']);
        self::assertSame(['zones' => ['department:75']], $body['audience']['include']);
    }

    public function testDoesNotPushNonPushableRow(): void
    {
        $uuid = Uuid::v4();
        $this->insertRow($uuid, 'transactional_message');

        $requests = [];
        ($this->handler($requests))(new PushTimelineFeedCommand($uuid));

        self::assertCount(0, $requests);
    }

    public function testNoopWhenRowNotFound(): void
    {
        $requests = [];
        ($this->handler($requests))(new PushTimelineFeedCommand(Uuid::v4()));

        self::assertCount(0, $requests);
    }

    private function handler(array &$requests): PushTimelineFeedCommandHandler
    {
        $http = new MockHttpClient(function (string $method, string $url, array $options) use (&$requests) {
            $requests[] = compact('method', 'url', 'options');

            return new MockResponse('{"external_id":"x","hot_score":1.0,"created":true}');
        }, 'https://indexer.test');

        return new PushTimelineFeedCommandHandler(
            $this->manager,
            new IndexerPayloadFactory(new NullLogger()),
            new IndexerClient($http, 'https://indexer.test', new NullLogger()),
        );
    }

    private function insertRow(Uuid $uuid, string $type, ?array $audience = null): void
    {
        $this->connection->executeStatement(
            'INSERT INTO timeline_feed (uuid, type, publication_date, event_date, audience, display, updated_at)
             VALUES (:uuid, :type, :pub, NULL, :audience, :display, :now)',
            [
                'uuid' => $uuid->toRfc4122(),
                'type' => $type,
                'pub' => new \DateTimeImmutable('2026-05-20 10:00:00'),
                'audience' => $audience,
                'display' => ['objectID' => $uuid->toRfc4122()],
                'now' => new \DateTimeImmutable(),
            ],
            [
                'pub' => Types::DATETIME_IMMUTABLE,
                'audience' => Types::JSON,
                'display' => Types::JSON,
                'now' => Types::DATETIME_IMMUTABLE,
            ],
        );
    }
}
