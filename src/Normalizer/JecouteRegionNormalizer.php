<?php

namespace App\Normalizer;

use App\Entity\Jecoute\Region;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class JecouteRegionNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'REGION_NORMALIZER_ALREADY_CALLED';

    public function __construct(private readonly FilesystemOperator $defaultStorage)
    {
    }

    /**
     * @param Region $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        if (\in_array('jecoute_region_read', $context['groups'] ?? [])) {
            $data['logo'] = $object->hasLogoUploaded() ? $this->getUrl($object->getLogoPathWithDirectory()) : null;
            $data['banner'] = $object->hasBannerUploaded() ? $this->getUrl($object->getBannerPathWithDirectory()) : null;
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return !isset($context[self::ALREADY_CALLED]) && $data instanceof Region;
    }

    private function getUrl(string $path): string
    {
        return $this->defaultStorage->publicUrl($path);
    }
}
