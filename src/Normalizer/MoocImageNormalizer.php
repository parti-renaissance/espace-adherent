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

    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $mooc = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        $mooc['image'] = $object->getListImage()
            ? $this->urlGenerator->generate('asset_url', ['path' => $object->getListImage()->getFilePath()], UrlGeneratorInterface::ABSOLUTE_URL)
            : $object->getYoutubeThumbnail();

        return $mooc;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Mooc::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return
            !isset($context[__CLASS__])
            && $data instanceof Mooc
            && \in_array('mooc_list', $context['groups'] ?? []);
    }
}
