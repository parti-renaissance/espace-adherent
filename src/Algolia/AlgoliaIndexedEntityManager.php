<?php

namespace App\Algolia;

use Algolia\SearchBundle\SearchService as SearchServiceInterface;
use App\Entity\AlgoliaIndexedEntityInterface;
use Doctrine\ORM\EntityManagerInterface;

class AlgoliaIndexedEntityManager
{
    private $algolia;
    private $entityManager;

    public function __construct(SearchServiceInterface $algolia, EntityManagerInterface $manager)
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
    }

    private function unIndex(AlgoliaIndexedEntityInterface $entity): void
    {
        $this->algolia->remove($this->entityManager, $entity);
    }
}
