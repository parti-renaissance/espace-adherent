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

    private const ALREADY_CALLED = 'PROCURATION_ACTION_NORMALIZER_ALREADY_CALLED';

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function normalize($object, ?string $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        if (\array_key_exists('author_scope', $data)) {
            $translationKey = 'role.'.$data['author_scope'];
            $translatedKey = $this->translator->trans('role.'.$data['author_scope']);

            $data['author_scope'] = $translatedKey !== $translationKey ? $translatedKey : $data['author_scope'];
        }

        return $data;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return
            empty($context[self::ALREADY_CALLED])
            && $data instanceof AbstractAction;
    }
}
