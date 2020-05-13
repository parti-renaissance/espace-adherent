<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\PersistentCollection;

trait LazyCollectionTrait
{
    private function isCollectionLoaded(Collection $collection): bool
    {
        return $collection instanceof ArrayCollection
            || $collection instanceof PersistentCollection && $collection->isInitialized()
        ;
    }
}
