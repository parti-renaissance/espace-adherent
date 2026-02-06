<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Adherent\Tag\TagEnum;
use App\Adherent\Tag\TagTranslator;
use App\Adherent\Tag\TranslatedTagInterface;
use App\Entity\NationalEvent\EventInscription;
use App\Entity\Procuration\AbstractProcuration;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TranslateAdherentTagNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const ENABLE_TAG_TRANSLATOR = 'enable_tag_translator';
    public const NO_STATIC_TAGS = 'no_static_tags';

    public function __construct(private readonly TagTranslator $tagTranslator, private readonly Security $security)
    {
    }

    public function normalize($object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        if (\is_array($data) && \array_key_exists('tags', $data)) {
            if (empty($data['tags']) && ($object instanceof AbstractProcuration || $object instanceof EventInscription)) {
                $data['tags'] = ['externe'];
            }

            $callback = fn (string $tag) => $this->tagTranslator->trans($tag, false);

            if ($object instanceof TranslatedTagInterface) {
                $appVersion = $this->security->getUser()?->getAuthAppVersion();

                $callback = function (string $tag) use ($appVersion, $context) {
                    if (
                        empty($context[self::NO_STATIC_TAGS])
                        || \in_array($tag, array_merge(TagEnum::getElectTags(), TagEnum::getAdherentTags()), true)
                    ) {
                        return [
                            'code' => $appVersion && $appVersion < 6000000 ? str_replace('plus_a_jour:annee_', 'a_jour_', $tag) : $tag,
                            'label' => $this->tagTranslator->trans($tag, false),
                            'type' => TagEnum::getMainLevel($tag),
                        ];
                    }

                    return null;
                };
            }

            $data['tags'] = array_values(array_filter(array_map($callback, $data['tags'] ?? [])));
        }

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => false,
        ];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return
            !isset($context[__CLASS__])
            && !empty($context[self::ENABLE_TAG_TRANSLATOR])
            && $this->validateType($data);
    }

    private function validateType($data): bool
    {
        return
            (\is_array($data) && \array_key_exists('tags', $data))
            || $data instanceof TranslatedTagInterface;
    }
}
