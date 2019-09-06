<?php

namespace Tests\AppBundle\Test\Producer\ChezVous;

use AppBundle\Entity\ChezVous\MeasureType;
use AppBundle\Producer\ChezVous\AlgoliaProducerInterface;

class AlgoliaNullProducer implements AlgoliaProducerInterface
{
    public function dispatchMeasureTypeUpdate(MeasureType $measureType): void
    {
    }
}
