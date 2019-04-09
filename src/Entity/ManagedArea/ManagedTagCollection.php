<?php

namespace AppBundle\Entity\ManagedArea;

use AppBundle\Entity\ReferentTag;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

trait ManagedTagCollection
{
    /**
     * @var ReferentTag[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\ReferentTag")
     */
    private $tags;

    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(ReferentTag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
    }

    public function removeTag(ReferentTag $tag): void
    {
        $this->tags->removeElement($tag);
    }
}
