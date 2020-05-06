<?php

namespace Tests\App\Test\Producer\ChezVous;

use App\Entity\ChezVous\MeasureType;
use App\Producer\ChezVous\AlgoliaProducerInterface;

class AlgoliaNullProducer implements AlgoliaProducerInterface
{
    public function dispatchMeasureTypeUpdated(MeasureType $measureType): void
    {
    }

    public function dispatchMeasureTypeDeleted(MeasureType $measureType): void
    {
    }
}
