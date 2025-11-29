<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\Committee;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CommitteeNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    /** @param Committee $object */
    public function normalize($object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        if (\array_key_exists('animator', $data) && \is_array($data['animator']) && !empty($data['animator'])) {
            $data['animator']['role'] = $this->translator->trans('role.animator', ['gender' => $object->animator?->getGender()]);
        }

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Committee::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && $data instanceof Committee;
    }
}
