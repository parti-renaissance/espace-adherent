<?php

namespace AppBundle\Entity\AdherentMessage\Filter;

use AppBundle\Entity\AdherentSegment;

interface AdherentSegmentAwareFilterInterface
{
    public function getAdherentSegment(): ?AdherentSegment;

    public function setAdherentSegment(?AdherentSegment $adherentSegment): void;
}
