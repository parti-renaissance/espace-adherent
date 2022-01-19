<?php

namespace App\Normalizer;

use App\Entity\Event\CommitteeEvent;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\String\ByteString;

/**
 * This normalizer adds some address fields on CommitteeEvent Sync process
 */
class ApiSyncCommitteeEventNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const GROUP = 'event_sync';
    private const SYNC_COMMITTEE_EVENT_NORMALIZER_ALREADY_CALLED = 'SYNC_COMMITTEE_EVENT_NORMALIZER_ALREADY_CALLED';

    /** @param CommitteeEvent $object */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::SYNC_COMMITTEE_EVENT_NORMALIZER_ALREADY_CALLED] = true;
        $data = $this->normalizer->normalize($object, $format, $context);

        $data['tags'] = $object->getReferentTagsCodes();

        $newData = [];

        foreach ($data as $key => $value) {
            $newData[(new ByteString($key))->camel()->toString()] = $value;
        }

        return $newData;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return empty($context[self::SYNC_COMMITTEE_EVENT_NORMALIZER_ALREADY_CALLED])
            && $data instanceof CommitteeEvent
            && \in_array(self::GROUP, $context['groups'] ?? [])
        ;
    }
}
