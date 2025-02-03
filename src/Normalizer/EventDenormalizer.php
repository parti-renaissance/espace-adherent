<?php

namespace App\Normalizer;

use ApiPlatform\Metadata\HttpOperation;
use App\Address\GeoCoder;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\CommitteeEvent;
use App\Entity\Event\DefaultEvent;
use App\Event\EventTypeEnum;
use App\Scope\ScopeEnum;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class EventDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function __construct(private readonly ScopeGeneratorResolver $scopeGeneratorResolver)
    {
    }

    public function denormalize($data, $type, $format = null, array $context = []): mixed
    {
        if (!empty($context[AbstractNormalizer::OBJECT_TO_POPULATE])) {
            $eventClass = \get_class($context[AbstractNormalizer::OBJECT_TO_POPULATE]);
        } else {
            $eventClass = $this->getEventClassFromType($data['type'] ?? null);
        }

        unset($data['type']);

        $context['resource_class'] = $eventClass;
        /** @var HttpOperation $operation */
        $operation = $context['operation'];
        $context['operation'] = $operation->withClass($eventClass);

        /** @var BaseEvent $object */
        $object = $this->denormalizer->denormalize($data, $eventClass, $format, $context);

        if (\in_array($context['operation_name'] ?? null, ['_api_/v3/events_post', '_api_/v3/events/{uuid}_put'], true)) {
            $object->setRenaissanceEvent(true);
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
            $object->national = true;
        }

        return $object;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => null,
            BaseEvent::class => true,
        ];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return BaseEvent::class === $type;
    }

    private function getEventClassFromType(?string $eventType): ?string
    {
        return match ($eventType) {
            EventTypeEnum::TYPE_COMMITTEE => CommitteeEvent::class,
            default => DefaultEvent::class,
        };
    }
}
