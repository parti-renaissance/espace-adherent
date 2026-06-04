<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\Indexer\Command;

use App\JeMengage\Timeline\Indexer\Command\TimelineFeedPushCommand;
use App\JeMengage\Timeline\Indexer\IndexerClient;
use App\JeMengage\Timeline\Indexer\IndexerPayloadFactory;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Uid\Uuid;
use Tests\App\AbstractKernelTestCase;

class TimelineFeedPushCommandTest extends AbstractKernelTestCase
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

    public function testPushesPushableRowsInOneBatchAndExcludesNonPushable(): void
    {
        $event = Uuid::v4();
        $news = Uuid::v4();
        $nonPushable = Uuid::v4();
        $this->insertRow($event, 'event', ['include' => ['zones' => ['department:75']]]);
        $this->insertRow($news, 'news');
        $this->insertRow($nonPushable, 'transactional_message');

        $requests = [];
        $tester = new CommandTester($this->command($requests));
        $tester->execute([]);

        $tester->assertCommandIsSuccessful();
        self::assertCount(1, $requests, 'One bulk batch call.');
        self::assertStringEndsWith('/index/batch', $requests[0]['url']);

        $ids = array_column(json_decode($requests[0]['options']['body'], true), 'external_id');
        self::assertContains($event->toRfc4122(), $ids);
        self::assertContains($news->toRfc4122(), $ids);
        self::assertNotContains($nonPushable->toRfc4122(), $ids, 'Non-pushable types are never queried.');
        self::assertStringContainsString('2 pushed', $tester->getDisplay());
    }

    public function testAudienceRestrictedRowIsPushedWithFullTargeting(): void
    {
        $uuid = Uuid::v4();
        // A pushable row targeted by adherent_ids is forwarded as-is (the indexer is the matching
        // authority); nothing is dropped or skipped.
        $this->insertRow($uuid, 'event', ['include' => ['adherent_ids' => [1, 2], 'zones' => ['department:75']]]);

        $requests = [];
        $tester = new CommandTester($this->command($requests));
        $tester->execute([]);

        $body = json_decode($requests[0]['options']['body'], true);
        self::assertCount(1, $body);
        self::assertSame($uuid->toRfc4122(), $body[0]['external_id']);
        // MySQL JSON reorders object keys on storage, so compare order-insensitively.
        self::assertEquals(['adherent_ids' => [1, 2], 'zones' => ['department:75']], $body[0]['audience']['include']);
        self::assertStringContainsString('1 pushed, 0 skipped', $tester->getDisplay());
    }

    private function command(array &$requests): TimelineFeedPushCommand
    {
        $http = new MockHttpClient(function (string $method, string $url, array $options) use (&$requests) {
            $requests[] = compact('method', 'url', 'options');

            return new MockResponse('[]');
        }, 'https://indexer.test');

        return new TimelineFeedPushCommand(
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
