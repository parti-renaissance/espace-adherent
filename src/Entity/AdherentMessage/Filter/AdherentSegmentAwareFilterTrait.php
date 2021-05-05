<?php

namespace App\Entity\AdherentMessage\Filter;

use App\Entity\AdherentSegment;
use Doctrine\ORM\Mapping as ORM;

trait AdherentSegmentAwareFilterTrait
{
    /**
     * @var AdherentSegment|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\AdherentSegment")
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
