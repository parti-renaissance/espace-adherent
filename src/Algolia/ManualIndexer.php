<?php

namespace App\Algolia;

use Algolia\AlgoliaSearchBundle\Indexer\Indexer;
use Algolia\AlgoliaSearchBundle\Indexer\ManualIndexer as BaseManualIndexer;
use Doctrine\ORM\EntityManagerInterface;

class ManualIndexer implements ManualIndexerInterface
{
    private $indexer;
    private $manager;

    /**
     * @var BaseManualIndexer
     */
    private $manualIndexer;

    public function __construct(Indexer $indexer, EntityManagerInterface $manager)
    {
        $this->indexer = $indexer;
        $this->manager = $manager;
        $this->manualIndexer = $indexer->getManualIndexer($manager);
    }

    public function index($entities, array $options = []): void
    {
        $this->manualIndexer->index($entities, $options);
    }

    public function unIndex($entities, array $options = []): void
    {
        $this->manualIndexer->unIndex($entities, $options);
    }

    public function reIndex($entityName, array $options = []): int
    {
        $this->synchronizeEntitySettings($entityName);

        return $this->manualIndexer->reIndex($entityName, array_merge([
            'batchSize' => 3000,
            'safe' => true,
            'clearEntityManager' => true,
        ], $options));
    }

    private function synchronizeEntitySettings(string $entityName): void
    {
        $this->discoverEntity($entityName);

        if (!$settings = $this->getEntitySettings($entityName)) {
            return;
        }

        $this->updateEntitySettings($entityName, $settings);

        $this->indexer->waitForAlgoliaTasks();
    }

    private function discoverEntity(string $entityName): void
    {
        $this->indexer->discoverEntity($entityName, $this->manager);
    }

    private function getEntitySettings(string $entityName): array
    {
        if (!$settings = $this->indexer->getIndexSettings()[$entityName] ?? null) {
            return [];
        }

        return $settings->getIndex()->getAlgoliaSettings();
    }

    private function getIndexName(string $entityName): string
    {
        return $this->indexer->getAlgoliaIndexName($entityName);
    }

    private function updateEntitySettings(string $entityName, array $settings): void
    {
        $this->indexer->setIndexSettings(
            $this->getIndexName($entityName),
            $settings,
            ['adaptIndexName' => false]
        );
    }
}
