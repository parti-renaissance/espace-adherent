<?php

namespace Tests\AppBundle\Test\Algolia;

use AppBundle\Algolia\ManualIndexerInterface;

class DummyManualIndexer implements ManualIndexerInterface
{
    private $indexer;

    public function __construct(DummyIndexer $indexer)
    {
        $this->indexer = $indexer;
    }

    public function index($entities, array $options = []): void
    {
        $this->indexer->index($entities);
    }

    public function unIndex($entities, array $options = []): void
    {
        $this->indexer->unIndex($entities);
    }

    public function getCreations(): array
    {
        return $this->indexer->creations;
    }

    public function getUpdates(): array
    {
        return $this->indexer->updates;
    }

    public function getDeletions(): array
    {
        return $this->indexer->deletions;
    }
}
