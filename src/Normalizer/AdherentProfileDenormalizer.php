<?php

namespace App\Normalizer;

use App\AdherentProfile\AdherentProfile;
use App\FranceCities\FranceCities;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AdherentProfileDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'ADHERENT_PROFILE_DENORMALIZER_ALREADY_CALLED';

    private FranceCities $franceCities;

    public function __construct(FranceCities $franceCities)
    {
        $this->franceCities = $franceCities;
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;
        $groups = $context['groups'] ?? [];

        if (
            \in_array('profile_write', $groups, true)
            && \array_key_exists('address', $data)
        ) {
            $data['post_address'] = $data['address'];
        }

        return $this->denormalizer->denormalize($data, $class, $format, $context);
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return !isset($context[self::ALREADY_CALLED]) && is_a($type, AdherentProfile::class, true);
    }
}
