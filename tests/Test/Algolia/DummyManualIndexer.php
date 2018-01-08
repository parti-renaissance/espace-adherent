<?php

namespace Tests\AppBundle\Test\Algolia;

use AppBundle\Algolia\ManualIndexerInterface;
use Psr\Log\LoggerInterface;

class DummyManualIndexer implements ManualIndexerInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function index($entities, array $options = []): void
    {
        if ($this->logger) {
            $this->log('indexing', $entities);
        }
    }

    public function unIndex($entities, array $options = []): void
    {
        if ($this->logger) {
            $this->log('un-indexing', $entities);
        }
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
}
