<?php

namespace AppBundle\Entity\AdherentMessage\Filter;

use AppBundle\Entity\AdherentSegment;

trait AdherentSegmentAwareFilterTrait
{
    /**
     * @var AdherentSegment|null
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\AdherentSegment")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $adherentSegment;

    public function getAdherentSegment(): ?AdherentSegment
    {
        return $this->adherentSegment;
    }

    public function setAdherentSegment(?AdherentSegment $adherentSegment): void
    {
        $this->adherentSegment = $adherentSegment;
    }
}
