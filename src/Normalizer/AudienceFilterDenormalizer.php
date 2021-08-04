<?php

namespace App\Normalizer;

use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\Audience\AbstractAudience;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AudienceFilterDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const AUDIENCE_FILTER_DENORMALIZER_ALREADY_CALLED = 'AUDIENCE_FILTER_DENORMALIZER_ALREADY_CALLED';

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        if (isset($data['is_committee_member'])) {
            $data['include_adherents_no_committee'] = !$data['is_committee_member'];
            $data['include_adherents_in_committee'] = $data['is_committee_member'];
            unset($data['is_committee_member']);
        }

        $context[self::AUDIENCE_FILTER_DENORMALIZER_ALREADY_CALLED] = true;

        /** @var AbstractAudience $audience */
        $audience = $this->denormalizer->denormalize($data, AudienceFilter::class, $format, $context);

        return $audience;
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return
            empty($context[self::AUDIENCE_FILTER_DENORMALIZER_ALREADY_CALLED])
            && AudienceFilter::class === $type;
    }
}
