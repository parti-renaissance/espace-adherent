<?php

namespace App\Normalizer;

use App\Entity\AdherentMessage\Segment\AudienceSegment;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AudienceSegmentDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const AUDIENCE_SEGMENT_DENORMALIZER_ALREADY_CALLED = 'AUDIENCE_SEGMENT_DENORMALIZER_ALREADY_CALLED';

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $context[self::AUDIENCE_SEGMENT_DENORMALIZER_ALREADY_CALLED] = true;

        /** @var AudienceSegment $audienceSegment */
        $audienceSegment = $this->denormalizer->denormalize($data, $type, $format, $context);

        if (isset($context['operation_name']) && '_api_/v3/audience-segments/{uuid}_put' === $context['operation_name']) {
            $audienceSegment->setSynchronized(false);
        }

        return $audienceSegment;
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return
            empty($context[self::AUDIENCE_SEGMENT_DENORMALIZER_ALREADY_CALLED])
            && AudienceSegment::class === $type;
    }
}
