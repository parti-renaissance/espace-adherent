<?php

namespace App\Entity\AdherentMessage\Filter;

use App\Entity\AdherentSegment;

interface AdherentSegmentAwareFilterInterface
{
    public function getAdherentSegment(): ?AdherentSegment;

    public function setAdherentSegment(?AdherentSegment $adherentSegment): void;
}
