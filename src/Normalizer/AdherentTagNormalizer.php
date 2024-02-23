<?php

namespace App\Normalizer;

use App\Adherent\Tag\TagTranslator;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AdherentTagNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const ENABLE_TAG_TRANSLATOR = 'enable_tag_translator';
    private const ALREADY_CALLED = 'ADHERENT_TAG_NORMALIZER_ALREADY_CALLED';

    public function __construct(private readonly TagTranslator $tagTranslator)
    {
    }

    public function normalize($object, ?string $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        if (\is_array($data) && !empty($data['tags']) && \is_array($data['tags'])) {
            $data['tags'] = array_map(fn (string $tag) => $this->tagTranslator->trans($tag, false), $data['tags']);
        }

        return $data;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return \is_array($data) && empty($context[self::ALREADY_CALLED]) && !empty($context[self::ENABLE_TAG_TRANSLATOR]);
    }
}
