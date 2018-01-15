<?php

namespace Tests\AppBundle\Test\Algolia;

use Algolia\AlgoliaSearchBundle\Indexer\Indexer;

class DummyIndexer extends Indexer
{
    public $entitiesToIndex = [];
    public $entitiesToUnIndex = [];

    public function performBatchCreations(array $creations)
    {
        $this->entitiesToIndex = array_merge_recursive($this->entitiesToIndex, $creations);
    }

    public function performBatchUpdates(array $updates)
    {
        $this->entitiesToIndex = array_merge_recursive($this->entitiesToIndex, $updates);
    }

    public function performBatchDeletions(array $deletions)
    {
        $this->entitiesToUnIndex = array_merge_recursive($this->entitiesToUnIndex, $deletions);
    }

    public function getAlgoliaIndexName($entity_or_class)
    {
        return sprintf('%s_test', parent::getAlgoliaIndexName($entity_or_class));
    }

    public function makeEnvIndexName($indexName, $perEnvironment)
    {
        return sprintf('%s_test', parent::makeEnvIndexName($indexName, $perEnvironment));
    }

    public function getEntitiesToIndex(): array
    {
        return $this->entitiesToIndex;
    }

    public function getEntitiesToUnIndex(): array
    {
        return $this->entitiesToUnIndex;
    }

    public function reset(): void
    {
        $this->entitiesToIndex = [];
        $this->entitiesToUnIndex = [];
    }
}
