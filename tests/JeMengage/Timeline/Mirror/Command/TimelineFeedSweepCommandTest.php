<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\Mirror\Command;

use Doctrine\DBAL\Connection;
use Tests\App\AbstractCommandTestCase;

class TimelineFeedSweepCommandTest extends AbstractCommandTestCase
{
    private ?Connection $connection = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->manager->getConnection();
    }

    protected function tearDown(): void
    {
        $this->connection = null;

        parent::tearDown();
    }

    public function testSweepRemovesRowsNotRefreshedSinceThreshold(): void
    {
        $this->insertRow('sweep-stale-row', '2000-01-01 00:00:00');
        $this->insertRow('sweep-fresh-row', '2999-01-01 00:00:00');

        $this->runCommand('app:timeline:sweep', [
            '--before' => '2010-01-01 00:00:00',
            '--force' => true,
        ])->assertCommandIsSuccessful();

        self::assertFalse($this->rowExists('sweep-stale-row'), 'A row older than the threshold must be swept.');
        self::assertTrue($this->rowExists('sweep-fresh-row'), 'A row refreshed after the threshold must survive.');
    }

    private function insertRow(string $uuid, string $updatedAt): void
    {
        $this->connection->executeStatement(
            "INSERT INTO timeline_feed (uuid, type, publication_date, display, updated_at)
             VALUES (:uuid, 'news', '2000-01-01 00:00:00', '{}', :updatedAt)",
            ['uuid' => $uuid, 'updatedAt' => $updatedAt],
        );
    }

    private function rowExists(string $uuid): bool
    {
        return false !== $this->connection->fetchOne(
            'SELECT id FROM timeline_feed WHERE uuid = :uuid',
            ['uuid' => $uuid],
        );
    }
}
