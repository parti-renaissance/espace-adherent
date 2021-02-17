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

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        /** @var ExposedImageOwnerInterface $object */
        $data = $this->normalizer->normalize($object, $format, $context);

        if (\in_array(self::NORMALIZATION_GROUP, $context['groups'])) {
            $data['image_url'] = $object->getImageName() ? $this->urlGenerator->generate(
                'asset_url',
                ['path' => $object->getImagePath()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ) : null;
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return !isset($context[self::ALREADY_CALLED]) && $data instanceof ExposedImageOwnerInterface;
    }
}
