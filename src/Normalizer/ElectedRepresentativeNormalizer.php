<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Mailchimp\Synchronisation\ElectedRepresentativeTagsBuilder;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ElectedRepresentativeNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(private readonly ElectedRepresentativeTagsBuilder $tagsBuilder)
    {
    }

    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        if (\in_array('elected_representative_change_diff', $context['groups'] ?? [])) {
            $data['activeTagCodes'] = $this->tagsBuilder->buildTags($object);
        }

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            ElectedRepresentative::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, $context = []): bool
    {
        return !isset($context[__CLASS__]) && $data instanceof ElectedRepresentative;
    }
}
