<?php

namespace App\Normalizer;

use App\Adherent\Tag\TagEnum;
use App\Adherent\Tag\TagTranslator;
use App\Entity\Projection\ManagedUser;
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
            $callback = fn (string $tag) => $this->tagTranslator->trans($tag, false);

            if ($object instanceof ManagedUser) {
                $callback = fn (string $tag) => ['label' => $this->tagTranslator->trans($tag, false), 'type' => TagEnum::getMainLevel($tag)];
            }

            $data['tags'] = array_map($callback, $data['tags']);
        }

        return $data;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return
            empty($context[self::ALREADY_CALLED])
            && !empty($context[self::ENABLE_TAG_TRANSLATOR])
            && (\is_array($data) || $data instanceof ManagedUser);
    }
}
