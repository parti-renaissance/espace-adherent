<?php

namespace AppBundle\Doctrine\DBAL;

use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;

class BatchedConnection
{
    private $connection;

    private $batchIndex;
    private $batchSize;

    /**
     * @var \Closure
     */
    private $onCommitCallback;

    /**
     * State that indicates if the batch has been started or not.
     *
     * @var bool
     */
    private $started;

    /**
     * @param \Closure $batchSizeReachedCallback is called each time the batch size is reached
     */
    public function __construct(Connection $connection, int $batchSize = 10000)
    {
        $this->connection = $connection;
        $this->batchSize = $batchSize;
        $this->started = false;
        $this->batchIndex = 0;
    }

    /**
     * $callback will be called when batch size is reached with the batch index as argument
     * $callback is called right after commit and before the new transaction.
     */
    public function setOnCommitCallback(\Closure $callback): void
    {
        $this->onCommitCallback = $callback;
    }

    public function startBatch(): void
    {
        if ($this->started) {
            throw new \LogicException('Batch already started');
        }

        $this->started = true;
        $this->batchIndex = 0;
        $this->connection->beginTransaction();
    }

    public function endBatch(): void
    {
        if (!$this->started) {
            throw new \LogicException('Batch has not been started');
        }

        $this->commitBatch();
        $this->started = false;
    }

    /**
     * Check \Doctrine\DBAL\Connection class if you want to know how to use this method.
     */
    public function executeUpdate($query, array $params = [], array $types = []): int
    {
        try {
            $result = $this->connection->executeUpdate($query, $params, $types);
        } catch (\Exception | \Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }

        $this->handleBatch();

        return $result;
    }

    /**
     * Check \Doctrine\DBAL\Connection class if you want to know how to use this method.
     */
    public function executeQuery($query, array $params = [], $types = [], QueryCacheProfile $qcp = null): Statement
    {
        try {
            $stmt = $this->connection->executeQuery($query, $params, $types, $qcp);
        } catch (\Exception | \Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }

        $this->handleBatch();

        return $stmt;
    }

    /**
     * Commit batch and call the user callback (if defined) when batch size is reached.
     */
    private function handleBatch(): void
    {
        if (!$this->started) {
            return;
        }

        ++$this->batchIndex;

        if (0 === $this->batchIndex % $this->batchSize) {
            $this->commitBatch();
            $this->connection->beginTransaction();
        }
    }

    private function commitBatch(): void
    {
        try {
            $this->connection->commit();

            if ($this->onCommitCallback) {
                ($this->onCommitCallback)($this->batchIndex);
            }
        } catch (\Exception | \Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }
}
