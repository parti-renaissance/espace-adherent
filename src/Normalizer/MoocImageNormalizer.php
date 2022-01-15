<?php

namespace App\Normalizer;

use App\Entity\Mooc\Mooc;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MoocImageNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;
    private const MOOC_IMAGE_NORMALIZER_ALREADY_CALLED = 'MOOC_IMAGE_NORMALIZER_ALREADY_CALLED';

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::MOOC_IMAGE_NORMALIZER_ALREADY_CALLED] = true;

        $mooc = $this->normalizer->normalize($object, $format, $context);

        $mooc['image'] = $object->getListImage()
            ? $this->urlGenerator->generate('asset_url', ['path' => $object->getListImage()->getFilePath()], UrlGeneratorInterface::ABSOLUTE_URL)
            : $object->getYoutubeThumbnail();

        return $mooc;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return
            empty($context[self::MOOC_IMAGE_NORMALIZER_ALREADY_CALLED])
            && $data instanceof Mooc
            && \in_array('mooc_list', $context['groups'] ?? []);
    }
}
