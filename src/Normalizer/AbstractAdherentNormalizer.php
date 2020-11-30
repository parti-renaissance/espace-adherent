<?php

namespace App\Normalizer;

use App\Entity\Adherent;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class AbstractAdherentNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    protected const ALREADY_CALLED = 'ADHERENT_NORMALIZER_ALREADY_CALLED';

    /**
     * @param Adherent $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[static::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        $data = $this->addBackwardCompatibilityFields($data);

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return !isset($context[static::ALREADY_CALLED]) && $data instanceof Adherent;
    }

    protected function addBackwardCompatibilityFields(array $data): array
    {
        if (\array_key_exists('postal_code', $data)) {
            $data['zipCode'] = $data['postal_code'];
        }

        if (\array_key_exists('first_name', $data)) {
            $data['firstName'] = $data['first_name'];
        }

        if (\array_key_exists('last_name', $data)) {
            $data['lastName'] = $data['last_name'];
        }

        return $data;
    }
}
