<?php

namespace App\Algolia;

use Algolia\SearchBundle\SearchService;
use App\Entity\AlgoliaIndexedEntityInterface;
use App\Entity\Timeline\Measure;
use Doctrine\ORM\EntityManagerInterface;

class AlgoliaIndexedEntityManager
{
    private $algolia;
    private $entityManager;

    public function __construct(SearchService $algolia, EntityManagerInterface $manager)
    {
        $this->algolia = $algolia;
        $this->entityManager = $manager;
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
        $this->algolia->index($this->entityManager, $entities);
    }

    private function index(AlgoliaIndexedEntityInterface $entity): void
    {
        $this->algolia->index($this->entityManager, $entity, $entity->getIndexOptions());

        if ($entity instanceof Measure) {
            $this->algolia->index($this->entityManager, $entity->getThemesToIndex()->toArray());
        }
    }

    private function unIndex(AlgoliaIndexedEntityInterface $entity): void
    {
        $this->algolia->remove($this->entityManager, $entity);

        if ($entity instanceof Measure) {
            $this->algolia->index($this->entityManager, $entity->getThemesToIndex()->toArray());
        }
    }
}
