<?php

declare(strict_types=1);

namespace App\Admin\Extension;

use App\Algolia\AlgoliaIndexedEntityManager;
use App\Entity\AlgoliaIndexedEntityInterface;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;

class AlgoliaIndexedEntityAdminExtension extends AbstractAdminExtension
{
    public function __construct(private readonly AlgoliaIndexedEntityManager $algoliaManager)
    {
    }

    /**
     * @param AlgoliaIndexedEntityInterface $object
     */
    public function postPersist(AdminInterface $admin, object $object): void
    {
        $this->algoliaManager->postPersist($object);
    }

    /**
     * @param AlgoliaIndexedEntityInterface $object
     */
    public function postUpdate(AdminInterface $admin, object $object): void
    {
        $this->algoliaManager->postUpdate($object);
    }

    /**
     * @param AlgoliaIndexedEntityInterface $object
     */
    public function preRemove(AdminInterface $admin, object $object): void
    {
        $this->algoliaManager->preRemove($object);
    }
}
