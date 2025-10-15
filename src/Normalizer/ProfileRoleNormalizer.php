<?php

namespace App\Normalizer;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProfileRoleNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;
    public const string NORMALIZER_GROUP = 'read:profile:role';

    /**
     * @param Adherent $object
     */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        $profileTags = [];
        $profileTags['région'] = $object->getZonesOfType(Zone::REGION, true)[0]?->getName();
        $profileTags['département'] = $object->getAssemblyZone()?->getName();
        $profileTags['comité'] = $object->getCommitteeMembership()?->getCommittee()?->getName();
        $profileTags['pad_dpt'] = $object->getPresidentDepartmentalAssemblyZones()[0]?->getName();

        $profileTags = array_filter($profileTags);

        array_walk($profileTags, static function (&$value, $key) {
            $value = \sprintf('%s:"%s"', $key, $value);
        });

        $data['profile_tags'] = array_values($profileTags);

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Adherent::class => false];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && $data instanceof Adherent && \in_array(self::NORMALIZER_GROUP, $context['groups'] ?? [], true);
    }
}
