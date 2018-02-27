<?php

namespace Tests\AppBundle\Doctrine\DBAL;

use AppBundle\Doctrine\DBAL\BatchedConnection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use PHPUnit\Framework\TestCase;

class BatchedConnectionTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Connection
     */
    private $connection;

    /**
     * @var BatchedConnection
     */
    private $batchedConnection;

    protected function setUp()
    {
        $this->connection = $this->createMock(Connection::class);
        $this->batchedConnection = new BatchedConnection($this->connection, 11);
    }

    public function testItCannotStartTwice(): void
    {
        $this->batchedConnection->startBatch();

        $this->expectException(\LogicException::class);

        $this->batchedConnection->startBatch();
    }

    public function testItCannotEndIfNotStarted(): void
    {
        $this->expectException(\LogicException::class);

        $this->batchedConnection->endBatch();
    }

    public function testItDoesNotUseTransactionsWhenBatchIsNotStarted(): void
    {
        $this->connection->expects($this->exactly(11))->method('executeUpdate')->willReturn(1);
        $this->connection->expects($this->exactly(11))->method('executeQuery')->willReturn($stmt = $this->createMock(Statement::class));
        $this->connection->expects($this->never())->method('beginTransaction');
        $this->connection->expects($this->never())->method('commit');
        $this->connection->expects($this->never())->method('rollBack');

        for ($i = 0; $i < 11; ++$i) {
            $this->assertSame(1, $this->batchedConnection->executeUpdate(''));
            $this->assertSame($stmt, $this->batchedConnection->executeQuery(''));
        }
    }

    public function testItDoesNotCommitUntilBatchSizeIsReached(): void
    {
        $this->connection->expects($this->exactly(5))->method('executeUpdate')->willReturn(1);
        $this->connection->expects($this->exactly(5))->method('executeQuery')->willReturn($stmt = $this->createMock(Statement::class));
        $this->connection->expects($this->once())->method('beginTransaction');
        $this->connection->expects($this->never())->method('commit');
        $this->connection->expects($this->never())->method('rollBack');

        $this->batchedConnection->setOnCommitCallback(function () {throw new \RuntimeException('Callback should not be called'); });
        $this->batchedConnection->startBatch();

        for ($i = 0; $i < 5; ++$i) {
            $this->assertSame(1, $this->batchedConnection->executeUpdate(''));
            $this->assertSame($stmt, $this->batchedConnection->executeQuery(''));
        }
    }

    public function testItCommitsWhenBatchSizeIsReached(): void
    {
        $this->connection->expects($this->exactly(6))->method('executeUpdate')->willReturn(1);
        $this->connection->expects($this->exactly(5))->method('executeQuery')->willReturn($stmt = $this->createMock(Statement::class));
        $this->connection->expects($this->exactly(2))->method('beginTransaction');
        $this->connection->expects($this->once())->method('commit');
        $this->connection->expects($this->never())->method('rollBack');

        $callbackCalled = false;
        $this->batchedConnection->setOnCommitCallback(function ($batchSize) use (&$callbackCalled) {
            $callbackCalled = true;
            $this->assertSame(11, $batchSize);
        });

        $this->batchedConnection->startBatch();

        for ($i = 0; $i < 5; ++$i) {
            $this->assertSame(1, $this->batchedConnection->executeUpdate(''));
            $this->assertSame($stmt, $this->batchedConnection->executeQuery(''));
        }

        $this->assertFalse($callbackCalled);
        $this->assertSame(1, $this->batchedConnection->executeUpdate(''));
        $this->assertTrue($callbackCalled);
    }

    public function testItEndBatchCloseCommitTransaction(): void
    {
        $this->connection->expects($this->exactly(2))->method('executeUpdate')->willReturn(1);
        $this->connection->expects($this->exactly(2))->method('executeQuery')->willReturn($stmt = $this->createMock(Statement::class));
        $this->connection->expects($this->once())->method('beginTransaction');
        $this->connection->expects($this->once())->method('commit');
        $this->connection->expects($this->never())->method('rollBack');

        $callbackCalled = false;
        $this->batchedConnection->setOnCommitCallback(function ($batchSize) use (&$callbackCalled) {
            $callbackCalled = true;
            $this->assertSame(2, $batchSize);
        });

        $this->batchedConnection->startBatch();

        $this->assertSame(1, $this->batchedConnection->executeUpdate(''));
        $this->assertSame($stmt, $this->batchedConnection->executeQuery(''));
        $this->assertFalse($callbackCalled);

        $this->batchedConnection->endBatch();

        $this->assertTrue($callbackCalled);
        $this->assertSame(1, $this->batchedConnection->executeUpdate(''));
        $this->assertSame($stmt, $this->batchedConnection->executeQuery(''));
    }

    public function testItRollbackOnExecuteUpdateException(): void
    {
        $this->connection->expects($this->once())->method('executeUpdate')->willThrowException(new \Exception());
        $this->connection->expects($this->once())->method('beginTransaction');
        $this->connection->expects($this->never())->method('commit');
        $this->connection->expects($this->once())->method('rollBack');
        $this->expectException(\Exception::class);

        $this->batchedConnection->startBatch();
        $this->batchedConnection->executeUpdate('');
    }

    public function testItRollbackOnExecuteQueryException(): void
    {
        $this->connection->expects($this->once())->method('executeQuery')->willThrowException(new \Exception());
        $this->connection->expects($this->once())->method('beginTransaction');
        $this->connection->expects($this->never())->method('commit');
        $this->connection->expects($this->once())->method('rollBack');
        $this->expectException(\Exception::class);

        $this->batchedConnection->startBatch();
        $this->batchedConnection->executeQuery('');
    }

    public function testItRollbackOnCommitException(): void
    {
        $this->connection->expects($this->exactly(11))->method('executeUpdate')->willReturn(1);
        $this->connection->expects($this->once())->method('beginTransaction');
        $this->connection->expects($this->once())->method('commit')->willThrowException(new \Exception());
        $this->connection->expects($this->once())->method('rollBack');
        $this->expectException(\Exception::class);

        $this->batchedConnection->startBatch();
        for ($i = 0; $i < 11; ++$i) {
            $this->batchedConnection->executeUpdate('');
        }
    }
}
