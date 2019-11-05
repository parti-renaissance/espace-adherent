<?php

namespace AppBundle\Twig;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Twig\Extension\RuntimeExtensionInterface;

class ProgrammaticFoundationRuntime implements RuntimeExtensionInterface
{
    public function sortByPosition(Collection $collection): Collection
    {
        $iterator = $collection->getIterator();
        $iterator->uasort(function ($a, $b) {
            return $a->getPosition() <=> $b->getPosition();
        });

        return new ArrayCollection(iterator_to_array($iterator));
    }
}
