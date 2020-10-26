<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @deprecated
 */
trait EntityReferentTagTrait
{
    /**
     * @var Collection|ReferentTag[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\ReferentTag")
     */
    protected $referentTags;

    /**
     * @return Collection|ReferentTag[]
     */
    public function getReferentTags(): Collection
    {
        return $this->referentTags;
    }

    public function addReferentTag(ReferentTag $referentTag): void
    {
        // Tries to keep zone synchronised with referent tag (both will coexist during the migration)
        if (method_exists($this, 'addZone')) {
            $this->addZone($referentTag->getZone());
        }

        if (!$this->referentTags->contains($referentTag)) {
            $this->referentTags->add($referentTag);
        }
    }

    public function removeReferentTag(ReferentTag $referentTag): void
    {
        // Tries to keep zone synchronised with referent tag (both will coexist during the migration)
        if (method_exists($this, 'removeZone')) {
            $this->removeZone($referentTag->getZone());
        }

        $this->referentTags->remove($referentTag);
    }

    public function clearReferentTags(): void
    {
        // Tries to keep zone synchronised with referent tag (both will coexist during the migration)
        if (method_exists($this, 'clearZones')) {
            $this->clearZones();
        }

        $this->referentTags->clear();
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("tags")
     * @JMS\Groups({"public", "committee_read", "citizen_project_read", "event_read", "citizen_action_read"})
     */
    public function getReferentTagsCodes(): array
    {
        return array_map(function (ReferentTag $referentTag) {
            return $referentTag->getCode();
        }, $this->referentTags->toArray());
    }
}
