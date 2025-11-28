<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\AdherentProfile\AdherentProfile;
use App\FranceCities\FranceCities;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AdherentProfileDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function __construct(private readonly FranceCities $franceCities)
    {
    }

    public function denormalize($data, $class, $format = null, array $context = []): mixed
    {
        $groups = $context['groups'] ?? [];

        if (
            \in_array('profile_write', $groups, true)
            && \array_key_exists('address', $data)
        ) {
            $data['post_address'] = $data['address'];
        }

        return $this->denormalizer->denormalize($data, $class, $format, $context + [__CLASS__ => true]);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            AdherentProfile::class => false,
        ];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && is_a($type, AdherentProfile::class, true);
    }
}
