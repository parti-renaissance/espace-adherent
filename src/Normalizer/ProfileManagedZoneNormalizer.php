<?php

namespace App\Normalizer;

use App\Entity\Adherent;
use App\Scope\ScopeEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProfileManagedZoneNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const GROUP = 'profile_managed_zone';

    /**
     * @param Adherent $object
     */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);
        $managedZone = null;

        if ($role = $object->findZoneBasedRole(ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY)) {
            $managedZone = [
                'type' => 'departement',
                'code' => $role->getZonesCodes()[0],
            ];
        }

        $data['managed_zone'] = $managedZone;

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Adherent::class => false];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return
            \in_array(self::GROUP, $context['groups'] ?? [], true)
            && !isset($context[__CLASS__])
            && $data instanceof Adherent;
    }
}
