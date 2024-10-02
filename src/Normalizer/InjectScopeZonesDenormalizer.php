<?php

namespace App\Normalizer;

use App\Entity\InjectScopeZonesInterface;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class InjectScopeZonesDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const INJECT_SCOPE_ZONES_DENORMALIZER_ALREADY_CALLED = 'INJECT_SCOPE_ZONES_DENORMALIZER_ALREADY_CALLED';

    public function __construct(private readonly ScopeGeneratorResolver $scopeGeneratorResolver)
    {
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $context[self::INJECT_SCOPE_ZONES_DENORMALIZER_ALREADY_CALLED] = true;

        /** @var InjectScopeZonesInterface $object */
        $object = $this->denormalizer->denormalize($data, $type, $format, $context);

        if ($object->getZones()->isEmpty() && $scope = $this->scopeGeneratorResolver->generate()) {
            $object->setZones($scope->getZones());
        }

        return $object;
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return
            empty($context[self::INJECT_SCOPE_ZONES_DENORMALIZER_ALREADY_CALLED])
            && is_a($type, InjectScopeZonesInterface::class, true);
    }
}
