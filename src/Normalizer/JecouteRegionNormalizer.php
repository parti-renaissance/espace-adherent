<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\Jecoute\Region;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class JecouteRegionNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(private readonly FilesystemOperator $defaultStorage)
    {
    }

    /**
     * @param Region $object
     */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        if (\in_array('jecoute_region_read', $context['groups'] ?? [])) {
            $data['logo'] = $object->hasLogoUploaded() ? $this->getUrl($object->getLogoPathWithDirectory()) : null;
            $data['banner'] = $object->hasBannerUploaded() ? $this->getUrl($object->getBannerPathWithDirectory()) : null;
        }

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Region::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && $data instanceof Region;
    }

    private function getUrl(string $path): string
    {
        return $this->defaultStorage->publicUrl($path);
    }
}
