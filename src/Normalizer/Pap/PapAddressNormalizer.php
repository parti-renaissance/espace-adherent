<?php

namespace App\Normalizer\Pap;

use App\Entity\Pap\Voter;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PapAddressNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'PAP_ADDRESS_NORMALIZER_ALREADY_CALLED';

    /**
     * @param Voter $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $voter = $this->normalizer->normalize($object, $format, $context);

        if (\array_key_exists('groups', $context) && \in_array('pap_address_voter_list', $context['groups'])) {
            $voter['first_name'] = $object->getFirstNameInitial();
        }

        return $voter;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return empty($context[self::ALREADY_CALLED]) && $data instanceof Voter;
    }
}
