<?php

namespace App\Normalizer;

use App\Entity\MunicipalEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MunicipalEventNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'MUNICIPAL_EVENT_NORMALIZER_ALREADY_CALLED';

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        if (isset($data['slug'])) {
            $data['url'] = $this->urlGenerator->generate('app_event_show', ['slug' => $data['slug']], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null, $context = [])
    {
        return !isset($context[self::ALREADY_CALLED]) && $data instanceof MunicipalEvent;
    }
}
