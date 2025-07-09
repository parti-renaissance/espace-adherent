<?php

namespace App\Algolia;

use Algolia\SearchBundle\SearchService as SearchServiceInterface;
use App\Entity\AlgoliaIndexedEntityInterface;
use Doctrine\ORM\EntityManagerInterface;

class AlgoliaIndexedEntityManager
{
    public function __construct(
        private readonly SearchServiceInterface $algolia,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function postPersist(AlgoliaIndexedEntityInterface $entity): void
    {
        $this->index([$entity]);
    }

    public function postUpdate(AlgoliaIndexedEntityInterface $entity): void
    {
        $this->index([$entity]);
    }

    public function preRemove(AlgoliaIndexedEntityInterface $entity): void
    {
        $this->unIndex($entity);
    }

    public function batch(array $entities): void
    {
        $this->index($entities);
    }

    private function index(array $entities): void
    {
        $this->algolia->index($this->entityManager, $entities);
    }

    private function unIndex(AlgoliaIndexedEntityInterface $entity): void
    {
        $this->algolia->remove($this->entityManager, $entity);
    }
}
