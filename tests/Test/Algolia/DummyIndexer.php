<?php

namespace Tests\AppBundle\Test\Algolia;

use Algolia\AlgoliaSearchBundle\Indexer\Indexer;
use Algolia\AlgoliaSearchBundle\Indexer\ManualIndexer;
use Doctrine\ORM\EntityManagerInterface;

class DummyIndexer extends Indexer
{
    public $creations = [];
    public $updates = [];
    public $deletions = [];

    /**
     * @var $manualIndexer ManualIndexer
     */
    private $manualIndexer;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();

        $this->manualIndexer = $this->getManualIndexer($em);
    }

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

    public function index($entities, array $options = []): void
    {
        $this->manualIndexer->index($entities, $options);
    }

    public function unIndex($entities, array $options = []): void
    {
        $this->manualIndexer->unIndex($entities);
    }
}
