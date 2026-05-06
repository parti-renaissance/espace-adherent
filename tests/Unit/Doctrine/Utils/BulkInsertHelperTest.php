<?php

declare(strict_types=1);

namespace Tests\App\Unit\Doctrine\Utils;

use App\Doctrine\Utils\BulkInsertHelper;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;

class BulkInsertHelperTest extends TestCase
{
    public function testInsertIgnoreWithEmptyRowsDoesNothingAndReturnsZero(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::never())->method('executeStatement');

        $helper = new BulkInsertHelper($connection);

        self::assertSame(0, $helper->insertIgnore('any_table', []));
    }

    public function testInsertIgnoreWithSingleRowBuildsCorrectSqlAndParams(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())
            ->method('executeStatement')
            ->with(
                'INSERT IGNORE INTO my_table (a,b,c) VALUES (?,?,?)',
                [1, 'foo', null],
            )
            ->willReturn(1);

        $helper = new BulkInsertHelper($connection);

        $result = $helper->insertIgnore('my_table', [
            ['a' => 1, 'b' => 'foo', 'c' => null],
        ]);

        self::assertSame(1, $result);
    }

    public function testInsertIgnoreWithMultipleRowsBuildsBatchSqlAndAggregatesParams(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())
            ->method('executeStatement')
            ->with(
                'INSERT IGNORE INTO adherent_message_targeted (message_id,adherent_id,targeted_at) VALUES (?,?,?),(?,?,?),(?,?,?)',
                [10, 1, '2026-05-05 10:00:00', 10, 2, '2026-05-05 10:00:00', 10, null, '2026-05-05 10:00:00'],
            )
            ->willReturn(3);

        $helper = new BulkInsertHelper($connection);

        $result = $helper->insertIgnore('adherent_message_targeted', [
            ['message_id' => 10, 'adherent_id' => 1, 'targeted_at' => '2026-05-05 10:00:00'],
            ['message_id' => 10, 'adherent_id' => 2, 'targeted_at' => '2026-05-05 10:00:00'],
            ['message_id' => 10, 'adherent_id' => null, 'targeted_at' => '2026-05-05 10:00:00'],
        ]);

        self::assertSame(3, $result);
    }

    public function testInsertIgnoreWithInvalidTableNameThrowsInvalidArgumentException(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::never())->method('executeStatement');

        $helper = new BulkInsertHelper($connection);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid table name: "evil; DROP TABLE users".');

        $helper->insertIgnore('evil; DROP TABLE users', [['a' => 1]]);
    }

    public function testInsertIgnoreWithMissingKeysInLaterRowsFillsNullForMissingValues(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())
            ->method('executeStatement')
            ->with(
                'INSERT IGNORE INTO t (a,b) VALUES (?,?),(?,?)',
                [1, 2, 3, null],
            )
            ->willReturn(2);

        $helper = new BulkInsertHelper($connection);

        $result = $helper->insertIgnore('t', [
            ['a' => 1, 'b' => 2],
            ['a' => 3], // 'b' missing → null
        ]);

        self::assertSame(2, $result);
    }
}
