<?php

namespace AppBundle\Normalizer;

use AppBundle\Entity\IdeasWorkshop\Theme;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class IdeaThemeNormalizer implements NormalizerInterface
{
    private $normalizer;
    private $urlGenerator;

    public function __construct(NormalizerInterface $normalizer, UrlGeneratorInterface $urlGenerator)
    {
        $this->normalizer = $normalizer;
        $this->urlGenerator = $urlGenerator;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        $data['thumbnail'] = $object->getImageName()
            ? $this->urlGenerator->generate(
                'asset_url',
                ['path' => $object->getImagePath()],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            : null
        ;

        return $data;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Theme;
    }
}
