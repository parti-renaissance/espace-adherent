<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\UserDocument;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserDocumentNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    /** @param UserDocument $object */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        $data['url'] = $this->urlGenerator->generate(
            'app_download_user_document',
            ['uuid' => $object->getUuid(), 'filename' => $object->getOriginalName()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [UserDocument::class => false];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__])
            && \in_array('user_document:read', $context['groups'] ?? [], true)
            && $data instanceof UserDocument;
    }
}
