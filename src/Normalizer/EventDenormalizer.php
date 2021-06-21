<?php

namespace App\Normalizer;

use App\Entity\Event\BaseEvent;
use App\Entity\Event\CauseEvent;
use App\Entity\Event\CoalitionEvent;
use App\Event\EventTypeEnum;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
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
            $eventType = $data['type'] ?? null;

            if (!$eventType || !($eventClass = $this->getEventClassFromType($eventType))) {
                throw new UnexpectedValueException('Type value is missing or invalid');
            }
        }

        if (!$eventClass) {
            throw new UnexpectedValueException('Type value is missing or invalid');
        }

        unset($data['type']);

        $visioUrl = $data['visio_url'] ?? null;
        if (\is_string($visioUrl) && !preg_match('~^[\w+.-]+://~', $visioUrl)) {
            $data['visio_url'] = 'https://'.$visioUrl;
        }

        $context['resource_class'] = $eventClass;

        return $this->denormalizer->denormalize($data, $eventClass, $format, $context);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return BaseEvent::class === $type;
    }

    private function getEventClassFromType(string $eventType): ?string
    {
        switch ($eventType) {
            case EventTypeEnum::TYPE_COALITION:
                return CoalitionEvent::class;
            case EventTypeEnum::TYPE_CAUSE:
                return CauseEvent::class;
        }

        return null;
    }
}
