<?php

namespace App\Normalizer;

use App\Entity\ExposedObjectInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ExposedObjectNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const URL_PARAM_NAME = 'link';

    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    /** @param ExposedObjectInterface $object */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        if (!isset($context[__CLASS__])) {
            $context[__CLASS__] = [];
        }

        $context[__CLASS__][] = $this->generateCacheKey($object);

        $data = $this->normalizer->normalize($object, $format, $context);

        $data[self::URL_PARAM_NAME] = $this->urlGenerator->generate(
            $object->getExposedRouteName(),
            $object->getExposedRouteParams(),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => null,
            ExposedObjectInterface::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return
            $data instanceof ExposedObjectInterface
            && (
                !isset($context[__CLASS__])
                || !\in_array($this->generateCacheKey($data), $context[__CLASS__])
            )
            && array_intersect($data->getNormalizationGroups(), $context['groups'] ?? []);
    }

    private function generateCacheKey(ExposedObjectInterface $object): string
    {
        return \sprintf('%s:%s', $object::class, $object->getId());
    }
}
