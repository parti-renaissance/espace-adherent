<?php

namespace App\Normalizer\ICal;

use App\Entity\Event\BaseEvent;
use App\Serializer\Encoder\ICalEncoder;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EventNormalizer implements NormalizerInterface
{
    /** @param BaseEvent $object */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $eventData = [
            'VEVENT' => [
                'UID' => $object->getUuid()->toString(),
                'SUMMARY' => $object->getName(),
                'DESCRIPTION' => $object->getDescription(),
                'DTSTART' => $object->getLocalBeginAt(),
                'DTEND' => $object->getLocalFinishAt(),
                'LOCATION' => $object->getInlineFormattedAddress(),
            ],
        ];

        if ($organizer = $object->getOrganizer()) {
            $eventData['ORGANIZER'] = \sprintf('%s %s', $organizer->getFirstName(), mb_strtoupper($organizer->getLastName()));
        }

        return $eventData;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => null,
            BaseEvent::class => true,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return ICalEncoder::FORMAT === $format && $data instanceof BaseEvent;
    }
}
