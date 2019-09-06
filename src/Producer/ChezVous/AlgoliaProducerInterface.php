<?php

namespace AppBundle\Producer\ChezVous;

use AppBundle\Entity\ChezVous\MeasureType;

interface AlgoliaProducerInterface
{
    public function dispatchMeasureTypeUpdated(MeasureType $measureType): void;

    public function dispatchMeasureTypeDeleted(MeasureType $measureType): void;
}
