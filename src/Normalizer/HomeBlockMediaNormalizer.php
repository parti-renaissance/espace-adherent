<?php

namespace App\Normalizer;

use App\Entity\HomeBlock;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class HomeBlockMediaNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;
    private const ALREADY_CALLED = 'HOME_BLOCK_MEDIA_ALREADY_CALLED';

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param HomeBlock $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        $data['media'] = $object->getMedia()
            ? [
                'type' => $object->getMedia()->isVideo() ? 'video' : 'image',
                'mime_type' => $object->getMedia()->getMimeType(),
                'path' => $this->urlGenerator->generate(
                    'asset_url',
                    ['path' => $object->getMedia()->getPathWithDirectory()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ]
            : null
        ;

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return !isset($context[self::ALREADY_CALLED])
            && $data instanceof HomeBlock
            && \in_array('home_block_list_read', $context['groups'] ?? [])
        ;
    }
}
