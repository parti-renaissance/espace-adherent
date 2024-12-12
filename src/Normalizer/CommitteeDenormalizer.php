<?php

namespace App\Normalizer;

use App\Entity\Committee;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class CommitteeDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function denormalize($data, string $class, ?string $format = null, array $context = [])
    {
        /** @var Committee $committee */
        $committee = $this->denormalizer->denormalize($data, $class, $format, $context + [__CLASS__ => true]);

        if (!$committee->isApproved()) {
            $committee->approved();
        }

        return $committee;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => null,
            Committee::class => false,
        ];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__])
            && Committee::class === $type
            && \in_array($context['operation_name'] ?? null, ['_api_/committees.{_format}_post', '_api_/committees/{uuid}_put'], true);
    }
}
