<?php

namespace AppBundle\Entity\AdherentMessage\Filter;

use AppBundle\Entity\ReferentTag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class AdherentZoneFilter extends AbstractAdherentMessageFilter
{
    /**
     * @var ReferentTag[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\ReferentTag")
     *
     * @Assert\Count(min=1)
     */
    private $referentTags;

    /**
     * @param ReferentTag[]
     */
    public function __construct(array $referentTags)
    {
        $this->referentTags = new ArrayCollection();

        $this->setReferentTags($referentTags);
    }

    /**
     * @return ReferentTag[]
     */
    public function getReferentTags(): array
    {
        return $this->referentTags->toArray();
    }

    public function addReferentTag(ReferentTag $tag): void
    {
        if (!$this->referentTags->contains($tag)) {
            $this->referentTags->add($tag);
        }
    }

    public function removeReferentTag(ReferentTag $tag): void
    {
        $this->referentTags->removeElement($tag);
    }

    public function setReferentTags(array $referentTags): void
    {
        foreach ($referentTags as $tag) {
            $this->addReferentTag($tag);
        }
    }
}
