<?php

namespace App\Normalizer;

use App\Entity\Audience\Audience;
use App\Geo\ManagedZoneProvider;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AudienceDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const AUDIENCE_DENORMALIZER_ALREADY_CALLED = 'AUDIENCE_DENORMALIZER_ALREADY_CALLED';

    private ScopeGeneratorResolver $scopeGeneratorResolver;
    private ManagedZoneProvider $managedZoneProvider;
    private Security $security;

    public function __construct(
        ScopeGeneratorResolver $scopeGeneratorResolver,
        ManagedZoneProvider $managedZoneProvider,
        Security $security,
    ) {
        $this->scopeGeneratorResolver = $scopeGeneratorResolver;
        $this->managedZoneProvider = $managedZoneProvider;
        $this->security = $security;
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $context[self::AUDIENCE_DENORMALIZER_ALREADY_CALLED] = true;

        /** @var Audience $audience */
        $audience = $this->denormalizer->denormalize($data, $type, $format, $context);
        $scope = $this->scopeGeneratorResolver->generate();
        $scopeCode = $scope ? $scope->getMainCode() : null;

        if (!empty($data['scope']) && $scopeCode === $data['scope']) {
            $audience->setZones($scope->getZones());
        }

        return $audience;
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return
            empty($context[self::AUDIENCE_DENORMALIZER_ALREADY_CALLED])
            && Audience::class === $type;
    }
}
