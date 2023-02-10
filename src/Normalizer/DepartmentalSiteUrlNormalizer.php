<?php

namespace App\Normalizer;

use App\Entity\DepartmentSite\DepartmentSite;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DepartmentalSiteUrlNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'DEPARTMENTAL_SITE_URL_NORMALIZER_ALREADY_CALLED';

    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        /** @var DepartmentSite $object */
        $data = $this->normalizer->normalize($object, $format, $context);

        $data['url'] = $object->getSlug() ? $this->urlGenerator->generate(
            'app_renaissance_department_site_view',
            ['slug' => $object->getSlug()],
            UrlGeneratorInterface::ABSOLUTE_URL
        ) : null;

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return !isset($context[self::ALREADY_CALLED]) && $data instanceof DepartmentSite;
    }
}
