<?php

namespace App\Entity\AdherentMessage\Filter;

use App\Entity\Geo\Zone;
use App\Entity\ReferentTag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class AudienceFilter extends AbstractUserFilter
{
    use BasicUserFiltersTrait;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $isCertified;

    /**
     * @var Zone
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Zone")
     */
    private $zone;

    /**
     * @var ReferentTag[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\ReferentTag")
     *
     * @Assert\Expression(
     *     "this.getZone() || count(this.getReferentTags()) > 0",
     *     message="Zone ou tags référent doivent être remplis."
     * )
     */
    private $referentTags;

    public function __construct()
    {
        $this->referentTags = new ArrayCollection();
    }

    /**
     * @return ReferentTag[]
     */
    public function getReferentTags(): array
    {
        return $this->referentTags->toArray();
    }

    public function getReferentTag(): ?ReferentTag
    {
        return $this->referentTags->first();
    }

    public function setReferentTags(array $referentTags): void
    {
        $this->referentTags->clear();

        foreach ($referentTags as $tag) {
            $this->addReferentTag($tag);
        }
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

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): void
    {
        $this->zone = $zone;
    }

    public function isCertified(): ?bool
    {
        return $this->isCertified;
    }

    public function setIsCertified(?bool $isCertified): void
    {
        $this->isCertified = $isCertified;
    }
}
