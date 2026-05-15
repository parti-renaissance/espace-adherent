<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\Video;
use App\Utils\VideoUrlBuilder;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class VideoNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(private readonly VideoUrlBuilder $urlBuilder)
    {
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        /** @var Video $object */
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        if (\is_array($data) && null !== $object->mediaPath) {
            $data['hls_url'] = $this->urlBuilder->videoHlsUrl($object);
            $data['preview_url'] = $this->urlBuilder->videoPreviewUrl($object);
            $data['thumbnail_url'] = $this->urlBuilder->videoThumbnailUrl($object);
        }

        return $data;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Video && !($context[__CLASS__] ?? false);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Video::class => false];
    }
}
