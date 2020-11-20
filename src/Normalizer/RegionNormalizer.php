<?php

namespace App\Normalizer;

use App\Entity\Jecoute\Region;
use App\Exception\InvalidUrlAdapterException;
use App\Storage\UrlAdapterInterface;
use League\Flysystem\Filesystem;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RegionNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'REGION_NORMALIZER_ALREADY_CALLED';

    /** @var UrlAdapterInterface */
    private $storageAdapter;

    public function __construct(Filesystem $storage)
    {
        $this->storageAdapter = $storage->getAdapter();

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
