<?php

namespace AppBundle\Admin;

use AppBundle\Algolia\AlgoliaIndexedEntityManager;
use AppBundle\Entity\AlgoliaIndexedEntityInterface;

trait AlgoliaIndexedEntityAdminTrait
{
    /**
     * @var AlgoliaIndexedEntityManager
     */
    private $algoliaManager;

    public function setAlgoliaManager(AlgoliaIndexedEntityManager $manager): void
    {
        $this->algoliaManager = $manager;
    }

    /**
     * @param AlgoliaIndexedEntityInterface $object
     */
    public function postPersist($object)
    {
        $this->algoliaManager->postPersist($object);
    }

    /**
     * @param AlgoliaIndexedEntityInterface $object
     */
    public function postUpdate($object)
    {
        $this->algoliaManager->postUpdate($object);
    }

    /**
     * @param AlgoliaIndexedEntityInterface $object
     */
    public function preRemove($object)
    {
        $this->algoliaManager->preRemove($object);
    }
}
