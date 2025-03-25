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

    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        /** @var DepartmentSite $object */
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        $data['url'] = $object->getSlug() ? $this->urlGenerator->generate(
            'app_renaissance_department_site_view',
            ['slug' => $object->getSlug()],
            UrlGeneratorInterface::ABSOLUTE_URL
        ) : null;

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            DepartmentSite::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && $data instanceof DepartmentSite;
    }
}
