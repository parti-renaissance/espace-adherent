<?php

namespace App\Normalizer;

use App\Entity\ExposedAdvancedImageOwnerInterface;
use App\Entity\ExposedImageOwnerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ImageOwnerExposedNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const NORMALIZATION_GROUP = 'image_owner_exposed';

    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function normalize($object, $format = null, array $context = [])
    {
        /** @var ExposedImageOwnerInterface|ExposedAdvancedImageOwnerInterface $object */
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__.$object::class => true]);

        $imageUrl = $object->getImageName() ? $this->urlGenerator->generate(
            'asset_url',
            ['path' => $object->getImagePath()],
            UrlGeneratorInterface::ABSOLUTE_URL
        ) : null;

        $data['image_url'] = $imageUrl;

        if ($object instanceof ExposedAdvancedImageOwnerInterface) {
            $data['image'] = $imageUrl ? [
                'url' => $imageUrl,
                'width' => $object->getImageWidth(),
                'height' => $object->getImageHeight(),
            ] : null;
        }

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => null,
            ExposedImageOwnerInterface::class => false,
            ExposedAdvancedImageOwnerInterface::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return
            \in_array(self::NORMALIZATION_GROUP, $context['groups'] ?? [])
            && (
                $data instanceof ExposedImageOwnerInterface
                || $data instanceof ExposedAdvancedImageOwnerInterface
            )
            && !isset($context[__CLASS__.$data::class]);
    }
}
