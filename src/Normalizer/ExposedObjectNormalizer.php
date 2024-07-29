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

    private const EXPOSED_OBJECT_NORMALIZER_ALREADY_CALLED = 'EXPOSED_OBJECT_NORMALIZER_ALREADY_CALLED';
    private const URL_PARAM_NAME = 'link';

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /** @param ExposedObjectInterface $object */
    public function normalize($object, $format = null, array $context = [])
    {
        if (!isset($context[self::EXPOSED_OBJECT_NORMALIZER_ALREADY_CALLED])) {
            $context[self::EXPOSED_OBJECT_NORMALIZER_ALREADY_CALLED] = [];
        }

        $context[self::EXPOSED_OBJECT_NORMALIZER_ALREADY_CALLED][] = $this->generateCacheKey($object);

        $data = $this->normalizer->normalize($object, $format, $context);

        $data[self::URL_PARAM_NAME] = $this->urlGenerator->generate(
            $object->getExposedRouteName(),
            $object->getExposedRouteParams(),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return
            $data instanceof ExposedObjectInterface
            && (
                empty($context[self::EXPOSED_OBJECT_NORMALIZER_ALREADY_CALLED])
                || !\in_array($this->generateCacheKey($data), $context[self::EXPOSED_OBJECT_NORMALIZER_ALREADY_CALLED])
            )
            && array_intersect($data->getNormalizationGroups(), $context['groups'] ?? []);
    }

    private function generateCacheKey(ExposedObjectInterface $object): string
    {
        return \sprintf('%s:%s', $object::class, $object->getId());
    }
}
