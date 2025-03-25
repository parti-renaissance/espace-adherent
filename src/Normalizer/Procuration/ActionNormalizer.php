<?php

namespace App\Normalizer\Procuration;

use App\Entity\ProcurationV2\AbstractAction;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ActionNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function normalize($object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        if (\array_key_exists('author_scope', $data)) {
            $translationKey = 'role.'.$data['author_scope'];
            $translatedKey = $this->translator->trans('role.'.$data['author_scope']);

            $data['author_scope'] = $translatedKey !== $translationKey ? $translatedKey : $data['author_scope'];
        }

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            AbstractAction::class => false,
        ];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && $data instanceof AbstractAction;
    }
}
