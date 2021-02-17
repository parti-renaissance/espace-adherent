<?php

namespace App\Normalizer;

use App\Entity\Coalition\Cause;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CauseNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'COALITION_NORMALIZER_ALREADY_CALLED';

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        /** @var Cause $object */
        $data = $this->normalizer->normalize($object, $format, $context);

        if (\in_array('cause_read', $context['groups'])) {
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
        return !isset($context[self::ALREADY_CALLED]) && $data instanceof Cause;
    }
}
