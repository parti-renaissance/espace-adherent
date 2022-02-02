<?php

namespace App\Normalizer;

use App\Entity\Adherent;
use App\Entity\EntityAdherentBlameableInterface;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AdherentBlameableDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'ADHERENT_BLAMEABLE_DENORMALIZER_ALREADY_CALLED';

    private Security $security;
    private ScopeGeneratorResolver $scopeGeneratorResolver;

    public function __construct(Security $security, ScopeGeneratorResolver $scopeGeneratorResolver)
    {
        $this->security = $security;
        $this->scopeGeneratorResolver = $scopeGeneratorResolver;
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $scope = $this->scopeGeneratorResolver->generate();
        $adherent = $scope && ($delegatedAccess = $scope->getDelegatedAccess())
            ? $delegatedAccess->getDelegator()
            : $this->security->getUser()
        ;

        $data = $this->denormalizer->denormalize($data, $class, $format, $context);
        if (!$data->getId()) {
            $data->setCreatedByAdherent($adherent);
        }
        $data->setUpdatedByAdherent($adherent);

        return $data;
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return is_a($type, EntityAdherentBlameableInterface::class, true) && $this->security->getUser() instanceof Adherent;
    }
}
