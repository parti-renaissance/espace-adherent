<?php

namespace App\Normalizer;

use App\Entity\Committee;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class CommitteeDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'JE_MENGAGE_WEB_COMMITTEE_DENORMALIZER_ALREADY_CALLED';

    public function denormalize($data, string $class, ?string $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        /** @var Committee $committee */
        $committee = $this->denormalizer->denormalize($data, $class, $format, $context);

        if (!$committee->isApproved()) {
            $committee->approved();
        }

        return $committee;
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = [])
    {
        return !isset($context[self::ALREADY_CALLED])
            && Committee::class === $type
            && \in_array($context['operation_name'] ?? null, ['_api_/committees.{_format}_post', '_api_/committees/{uuid}_put'], true);
    }
}
