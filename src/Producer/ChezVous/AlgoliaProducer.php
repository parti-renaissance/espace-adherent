<?php

namespace AppBundle\Producer\ChezVous;

use AppBundle\Entity\ChezVous\MeasureType;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class AlgoliaProducer extends Producer implements AlgoliaProducerInterface
{
    public const KEY_MEASURE_TYPE_UPDATED = 'measure_type.updated';
    public const KEY_MEASURE_TYPE_DELETED = 'measure_type_deleted';

    public function dispatchMeasureTypeUpdated(MeasureType $measureType): void
    {
        $this->publishMeasureType($measureType, self::KEY_MEASURE_TYPE_UPDATED);
    }

    public function dispatchMeasureTypeDeleted(MeasureType $measureType): void
    {
        $this->publishMeasureType($measureType, self::KEY_MEASURE_TYPE_DELETED);
    }

    private function publishMeasureType(MeasureType $measureType, string $routingKey): void
    {
        $this->publish(json_encode([
            'id' => $measureType->getId(),
        ]), $routingKey);
    }
}
