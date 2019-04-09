<?php

namespace AppBundle\Entity\ManagedArea;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="managed_area_referent")
 * @ORM\Entity
 */
class ReferentManagedArea extends ManagedArea
{
    use ManagedTagCollection;

    public function __construct(array $tags = [])
    {
        $this->tags = new ArrayCollection($tags);
    }

    public function isValid(): bool
    {
        return !$this->tags->isEmpty();
    }
}
