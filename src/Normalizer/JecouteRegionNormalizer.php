<?php

namespace App\Normalizer;

use App\Entity\Jecoute\Region;
use App\Exception\InvalidUrlAdapterException;
use App\Storage\UrlAdapterInterface;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class JecouteRegionNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'REGION_NORMALIZER_ALREADY_CALLED';

    /** @var UrlAdapterInterface */
    private $storageAdapter;

    public function __construct(FilesystemInterface $storage)
    {
        $this->storageAdapter = $storage->getAdapter();

        if ($this->storageAdapter instanceof CachedAdapter) {
            $this->storageAdapter = $this->storageAdapter->getAdapter();
        }

        if (!$this->storageAdapter instanceof UrlAdapterInterface) {
            throw new InvalidUrlAdapterException();
        }
    }

    /**
     * @param Region $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        $geoRegion = $object->getGeoRegion();

        $data['name'] = $geoRegion->getName();
        $data['code'] = $geoRegion->getCode();

        if (\in_array('jecoute_region_read', $context['groups'])) {
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
        return $this->storageAdapter->getUrl($path);
    }
}
