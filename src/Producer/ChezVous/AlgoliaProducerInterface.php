<?php

namespace App\Producer\ChezVous;

use App\Entity\ChezVous\MeasureType;

interface AlgoliaProducerInterface
{
    public function dispatchMeasureTypeUpdated(MeasureType $measureType): void;

    public function dispatchMeasureTypeDeleted(MeasureType $measureType): void;
}
