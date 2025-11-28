<?php

declare(strict_types=1);

namespace App\EntityListener;

use App\Algolia\AlgoliaIndexedEntityManager;
use App\Entity\AlgoliaIndexedEntityInterface;

class AlgoliaIndexListener
{
    public function __construct(private readonly AlgoliaIndexedEntityManager $manager)
    {
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
