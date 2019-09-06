<?php

namespace AppBundle\ChezVous;

use AppBundle\Entity\ChezVous\MeasureType;
use Symfony\Component\EventDispatcher\Event;

class MeasureTypeEvent extends Event
{
    private $measureType;

    public function __construct(MeasureType $measureType)
    {
        $this->measureType = $measureType;
    }

    public function getMeasureType(): MeasureType
    {
        return $this->measureType;
    }
}
