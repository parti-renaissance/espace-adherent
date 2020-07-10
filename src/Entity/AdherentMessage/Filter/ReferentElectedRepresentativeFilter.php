<?php

namespace App\Entity\AdherentMessage\Filter;

use App\Entity\ReferentTag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class ReferentElectedRepresentativeFilter extends AbstractElectedRepresentativeFilter
{
    /**
     * @var ReferentTag[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\ReferentTag")
     *
     * @Assert\NotNull
     */
    private $referentTags;

    public function __construct(array $referentTags)
    {
        $this->referentTags = new ArrayCollection();

        foreach ($referentTags as $tag) {
            $this->addReferentTag($tag);
        }
    }

    /**
     * @return ReferentTag[]
     */
    public function getReferentTags(): array
    {
        return $this->referentTags->toArray();
    }

    public function addReferentTag(ReferentTag $referentTag): void
    {
        if (!$this->referentTags->contains($referentTag)) {
            $this->referentTags->add($referentTag);
        }
    }

    public function removeReferentTag(ReferentTag $referentTag): void
    {
        $this->referentTags->removeElement($referentTag);
    }
}
