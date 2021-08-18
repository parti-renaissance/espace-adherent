<?php

namespace App\Normalizer;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Entity\Audience\Audience;
use App\Geo\ManagedZoneProvider;
use App\Scope\AuthorizationChecker;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AudienceDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const AUDIENCE_DENORMALIZER_ALREADY_CALLED = 'AUDIENCE_DENORMALIZER_ALREADY_CALLED';

    private $authorizationChecker;
    private $managedZoneProvider;
    private $security;

    public function __construct(
        AuthorizationChecker $authorizationChecker,
        ManagedZoneProvider $managedZoneProvider,
        Security $security
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->managedZoneProvider = $managedZoneProvider;
        $this->security = $security;
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $context[self::AUDIENCE_DENORMALIZER_ALREADY_CALLED] = true;

        /** @var Audience $audience */
        $audience = $this->denormalizer->denormalize($data, $type, $format, $context);

        if (
            !empty($data['scope'])
            && ($user = $this->security->getUser())
            && $this->authorizationChecker->isScopeGranted($data['scope'], $user)
        ) {
            $audience->setZones($this->managedZoneProvider->getManagedZones($user, AdherentSpaceEnum::SCOPES[$data['scope']]));
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
