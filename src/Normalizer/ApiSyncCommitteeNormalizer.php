<?php

namespace App\Normalizer;

use App\Entity\Committee;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\String\ByteString;

/**
 * This normalizer adds some address fields on Committee Sync process
 */
class ApiSyncCommitteeNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const GROUP = 'committee_sync';
    private const SYNC_COMMITTEE_NORMALIZER_ALREADY_CALLED = 'SYNC_COMMITTEE_NORMALIZER_ALREADY_CALLED';

    /** @param Committee $object */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::SYNC_COMMITTEE_NORMALIZER_ALREADY_CALLED] = true;
        $data = $this->normalizer->normalize($object, $format, $context);

        $data['zipCode'] = $object->getPostalCode();
        $data['city'] = $object->getCityName();
        $data['tags'] = $object->getReferentTagsCodes();
        $data['status'] = $object->getStatus();

        $newData = [];

        foreach ($data as $key => $value) {
            $newData[(new ByteString($key))->camel()->toString()] = $value;
        }

        return $newData;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return empty($context[self::SYNC_COMMITTEE_NORMALIZER_ALREADY_CALLED])
            && $data instanceof Committee
            && \in_array(self::GROUP, $context['groups'] ?? [])
        ;
    }
}
