<?php

namespace AppBundle\Producer\ChezVous;

use AppBundle\Entity\ChezVous\MeasureType;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class AlgoliaProducer extends Producer implements AlgoliaProducerInterface
{
    public function dispatchMeasureTypeUpdate(MeasureType $measureType): void
    {
        $this->publish(json_encode([
            'id' => $measureType->getId(),
        ]));
    }
}
