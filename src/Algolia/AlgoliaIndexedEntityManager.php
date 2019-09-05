<?php

namespace AppBundle\Algolia;

use AppBundle\Entity\AlgoliaIndexedEntityInterface;
use AppBundle\Entity\Timeline\Measure;

class AlgoliaIndexedEntityManager
{
    private $algolia;

    public function __construct(ManualIndexerInterface $algolia)
    {
        $this->algolia = $algolia;
    }

    public function postPersist(AlgoliaIndexedEntityInterface $entity): void
    {
        $this->index($entity);
    }

    public function postUpdate(AlgoliaIndexedEntityInterface $entity): void
    {
        $this->index($entity);
    }

    public function preRemove(AlgoliaIndexedEntityInterface $entity): void
    {
        $this->unIndex($entity);
    }

    public function batch(array $entities): void
    {
        $this->algolia->index($entities);
    }

    private function index(AlgoliaIndexedEntityInterface $entity): void
    {
        $this->algolia->index($entity);

        if ($entity instanceof Measure) {
            $this->algolia->index($entity->getThemesToIndex()->toArray());
        }
    }

    private function unIndex(AlgoliaIndexedEntityInterface $entity): void
    {
        $this->algolia->unIndex($entity);

        if ($entity instanceof Measure) {
            $this->algolia->index($entity->getThemesToIndex()->toArray());
        }
    }
}
