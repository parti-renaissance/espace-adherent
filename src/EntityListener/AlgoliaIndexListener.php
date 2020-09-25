<?php

namespace App\EntityListener;

use App\Algolia\AlgoliaIndexedEntityManager;
use App\Entity\AlgoliaIndexedEntityInterface;

class AlgoliaIndexListener
{
    private $manager;

    public function __construct(AlgoliaIndexedEntityManager $manager)
    {
        $this->manager = $manager;
    }

    public function postPersist(AlgoliaIndexedEntityInterface $entity): void
    {
        $this->manager->postPersist($entity);
    }

    public function postUpdate(AlgoliaIndexedEntityInterface $entity): void
    {
        $this->manager->postUpdate($entity);
    }

    public function preRemove(AlgoliaIndexedEntityInterface $entity): void
    {
        $this->manager->preRemove($entity);
    }
}
