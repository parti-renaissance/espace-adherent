<?php

namespace App\Normalizer;

use App\Entity\AdherentMessage\Segment\AudienceSegment;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AudienceSegmentDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []): mixed
    {
        /** @var AudienceSegment $audienceSegment */
        $audienceSegment = $this->denormalizer->denormalize($data, $type, $format, $context + [__CLASS__ => true]);

        if (isset($context['operation_name']) && '_api_/v3/audience-segments/{uuid}_put' === $context['operation_name']) {
            $audienceSegment->setSynchronized(false);
        }

        return $audienceSegment;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            AudienceSegment::class => false,
        ];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && AudienceSegment::class === $type;
    }
}
