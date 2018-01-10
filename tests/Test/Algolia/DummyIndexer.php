<?php

namespace Tests\AppBundle\Test\Algolia;

use Algolia\AlgoliaSearchBundle\Indexer\Indexer;

class DummyIndexer extends Indexer
{
    public $creations = array();
    public $updates = array();
    public $deletions = array();

    public function performBatchCreations(array $creations)
    {
        $this->creations = array_merge_recursive($this->creations, $creations);
    }

    public function performBatchUpdates(array $updates)
    {
        $this->updates = array_merge_recursive($this->updates, $updates);
    }

    public function performBatchDeletions(array $deletions)
    {
        $this->deletions = array_merge_recursive($this->deletions, $deletions);
    }

    public function getAlgoliaIndexName($entity_or_class)
    {
        return sprintf('%s_test', parent::getAlgoliaIndexName($entity_or_class));
    }

    public function makeEnvIndexName($indexName, $perEnvironment)
    {
        return sprintf('%s_test', parent::makeEnvIndexName($indexName, $perEnvironment));
    }
}
