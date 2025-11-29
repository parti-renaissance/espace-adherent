<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\InjectScopeZonesInterface;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class InjectScopeZonesDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function __construct(private readonly ScopeGeneratorResolver $scopeGeneratorResolver)
    {
    }

    public function denormalize($data, $type, $format = null, array $context = []): mixed
    {
        /** @var InjectScopeZonesInterface $object */
        $object = $this->denormalizer->denormalize($data, $type, $format, $context + [__CLASS__ => true]);

        if ($object->getZones()->isEmpty() && $scope = $this->scopeGeneratorResolver->generate()) {
            $object->setZones($scope->getZones());
        }

        return $object;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            InjectScopeZonesInterface::class => false,
        ];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && is_a($type, InjectScopeZonesInterface::class, true);
    }
}
