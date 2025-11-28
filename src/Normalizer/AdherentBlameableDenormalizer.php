<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\Adherent;
use App\Entity\EntityAdherentBlameableInterface;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AdherentBlameableDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function __construct(
        private readonly Security $security,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
    ) {
    }

    public function denormalize($data, $class, $format = null, array $context = []): mixed
    {
        $scope = $this->scopeGeneratorResolver->generate();
        $adherent = $scope && $scope->getDelegatedAccess() ? $scope->getDelegator() : $this->security->getUser();

        $data = $this->denormalizer->denormalize($data, $class, $format, $context + [__CLASS__ => true]);
        if (!$data->getId()) {
            $data->setCreatedByAdherent($adherent);
        }
        $data->setUpdatedByAdherent($adherent);

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            EntityAdherentBlameableInterface::class => false,
        ];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__])
            && is_a($type, EntityAdherentBlameableInterface::class, true)
            && $this->security->getUser() instanceof Adherent;
    }
}
