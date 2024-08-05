<?php

namespace App\Normalizer;

use App\Entity\ExposedImageOwnerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ImageOwnerExposedNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const NORMALIZATION_GROUP = 'image_owner_exposed';
    private const ALREADY_CALLED = 'IMAGE_OWNER_EXPOSED_NORMALIZER_ALREADY_CALLED';

    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED.$object::class] = true;

        /** @var ExposedImageOwnerInterface $object */
        $data = $this->normalizer->normalize($object, $format, $context);

        $data['image_url'] = $object->getImageName() ? $this->urlGenerator->generate(
            'asset_url',
            ['path' => $object->getImagePath()],
            UrlGeneratorInterface::ABSOLUTE_URL
        ) : null;

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return
            \in_array(self::NORMALIZATION_GROUP, $context['groups'] ?? [])
            && $data instanceof ExposedImageOwnerInterface
            && !isset($context[self::ALREADY_CALLED.$data::class]);
    }
}
