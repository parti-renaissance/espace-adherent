<?php

namespace App\Normalizer;

use App\Address\GeoCoder;
use App\Entity\Event\Event;
use App\Scope\ScopeEnum;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class EventDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function __construct(
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly \HTMLPurifier $eventPurifier,
    ) {
    }

    public function denormalize($data, $type, $format = null, array $context = []): mixed
    {
        /** @var Event $object */
        $object = $this->denormalizer->denormalize($data, $type, $format, $context + [__CLASS__ => true]);

        if (!empty($data['description'])) {
            $object->setDescription($this->eventPurifier->purify($data['description']));
        }

        if (GeoCoder::DEFAULT_TIME_ZONE !== $object->getTimeZone()) {
            $timeZone = new \DateTimeZone($object->getTimeZone());

            if ($date = $object->getBeginAt()) {
                $object->setBeginAt((new \DateTime($date->format('Y-m-d H:i:s'), $timeZone))->setTimezone(new \DateTimeZone(GeoCoder::DEFAULT_TIME_ZONE)));
            }

            if ($date = $object->getFinishAt()) {
                $object->setFinishAt((new \DateTime($date->format('Y-m-d H:i:s'), $timeZone))->setTimezone(new \DateTimeZone(GeoCoder::DEFAULT_TIME_ZONE)));
            }
        }

        if (($scope = $this->scopeGeneratorResolver->generate()) && ScopeEnum::NATIONAL === $scope->getMainCode()) {
            $object->setNational(true);
        }

        return $object;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => null,
            Event::class => false,
        ];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && Event::class === $type;
    }
}
