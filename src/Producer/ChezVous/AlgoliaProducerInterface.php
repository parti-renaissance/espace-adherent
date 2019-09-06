<?php

namespace AppBundle\Producer\ChezVous;

use AppBundle\Entity\ChezVous\MeasureType;

interface AlgoliaProducerInterface
{
    public function dispatchMeasureTypeUpdate(MeasureType $measureType): void;
}
