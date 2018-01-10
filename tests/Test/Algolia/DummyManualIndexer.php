<?php

namespace Tests\AppBundle\Test\Algolia;

use AppBundle\Algolia\ManualIndexerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class DummyManualIndexer implements ManualIndexerInterface
{
    private $logger;
    private $indexer;
    private $manualIndexer;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        $this->indexer = new DummyIndexer();
        $this->manualIndexer = $this->indexer->getManualIndexer($em);
    }

    public function index($entities, array $options = []): void
    {
        if ($this->logger) {
            $this->log('indexing', $entities);
        }

        $this->manualIndexer->index($entities);
    }

    public function unIndex($entities, array $options = []): void
    {
        if ($this->logger) {
            $this->log('un-indexing', $entities);
        }

        $this->manualIndexer->unIndex($entities);
    }

    public function log(string $action, $entities): void
    {
        if (is_array($entities)) {
            $this->logger->info(sprintf("[algolia] $action %d entities.", count($entities)));
        } elseif (is_string($entities)) {
            $this->logger->info("[algolia] $action \"$entities\".");
        } elseif (is_object($entities)) {
            $this->logger->info(sprintf("[algolia] $action entity \"%s\".", get_class($entities)));
        }
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
