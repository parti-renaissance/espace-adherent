<?php

declare(strict_types=1);

namespace App\Entity\AdherentMessage\Filter;

use App\Entity\AdherentSegment;
use Doctrine\ORM\Mapping as ORM;

trait AdherentSegmentAwareFilterTrait
{
    /**
     * @var AdherentSegment|null
     */
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: AdherentSegment::class)]
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
