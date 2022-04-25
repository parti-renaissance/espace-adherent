<?php

namespace App\Normalizer;

use App\Entity\Event\BaseEvent;
use App\Entity\Event\CauseEvent;
use App\Entity\Event\CoalitionEvent;
use App\Entity\Event\DefaultEvent;
use App\Event\EventTypeEnum;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class EventDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        if (!empty($context[AbstractNormalizer::OBJECT_TO_POPULATE])) {
            $eventClass = \get_class($context[AbstractNormalizer::OBJECT_TO_POPULATE]);
        } else {
            $eventClass = $this->getEventClassFromType($data['type'] ?? null);
        }

        unset($data['type']);

        $context['resource_class'] = $eventClass;

        return $this->denormalizer->denormalize($data, $eventClass, $format, $context);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return BaseEvent::class === $type;
    }

    private function getEventClassFromType(?string $eventType): ?string
    {
        switch ($eventType) {
            case EventTypeEnum::TYPE_COALITION:
                return CoalitionEvent::class;
            case EventTypeEnum::TYPE_CAUSE:
                return CauseEvent::class;
            default:
                return DefaultEvent::class;
        }
    }
}
