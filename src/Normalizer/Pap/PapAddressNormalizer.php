<?php

namespace App\Normalizer\Pap;

use App\Entity\Pap\Voter;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PapAddressNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param Voter $object
     */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $voter = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        if (\array_key_exists('groups', $context) && \in_array('pap_address_voter_list', $context['groups'])) {
            $voter['first_name'] = $object->getFirstNameInitial();
        }

        return $voter;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Voter::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && $data instanceof Voter;
    }
}
