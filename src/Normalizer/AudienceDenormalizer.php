<?php

namespace App\Normalizer;

use App\Audience\AudienceTypeEnum;
use App\Entity\Audience\AbstractAudience;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AudienceDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const AUDIENCE_DENORMALIZER_ALREADY_CALLED = 'AUDIENCE_DENORMALIZER_ALREADY_CALLED';

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        if (!empty($context[AbstractNormalizer::OBJECT_TO_POPULATE])) {
            $audienceClass = \get_class($context[AbstractNormalizer::OBJECT_TO_POPULATE]);
        } else {
            $audienceType = $data['type'] ?? null;

            if (!$audienceType || !($audienceClass = $this->getAudienceClassFromType($audienceType))) {
                throw new UnexpectedValueException('Type value is missing or invalid');
            }
        }

        if (!$audienceClass) {
            throw new UnexpectedValueException('Type value is missing or invalid');
        }

        unset($data['type']);

        $context[self::AUDIENCE_DENORMALIZER_ALREADY_CALLED] = true;

        return $this->denormalizer->denormalize($data, $audienceClass, $format, $context);
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return
            empty($context[self::AUDIENCE_DENORMALIZER_ALREADY_CALLED])
            && AbstractAudience::class === $type;
    }

    private function getAudienceClassFromType(string $type): ?string
    {
        if (!isset(AudienceTypeEnum::CLASSES[$type])) {
            throw new \InvalidArgumentException(sprintf('Audience type "%s" is undefined', $type));
        }

        return AudienceTypeEnum::CLASSES[$type];
    }
}
