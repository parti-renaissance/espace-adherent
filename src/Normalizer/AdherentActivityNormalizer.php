<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Adherent\Activity\AdherentActivityLabels;
use App\Adherent\Activity\SourceTypeEnum;
use App\Entity\Adherent\Activity\AdherentActivity;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AdherentActivityNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /** @param AdherentActivity $object */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        if (!\is_array($data)) {
            return $data;
        }

        $data['event_label'] = AdherentActivityLabels::EVENT_TYPES[$object->eventType] ?? $object->eventType;
        $data['description'] = $this->buildSentence($object);

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            AdherentActivity::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof AdherentActivity && !isset($context[__CLASS__]);
    }

    private function buildSentence(AdherentActivity $activity): ?string
    {
        if (SourceTypeEnum::Hit !== $activity->sourceType) {
            return null;
        }

        $displayName = $activity->adherent?->getFirstName() ?: '';
        if ('' === $displayName) {
            return null;
        }

        $metadata = \is_array($activity->metadata) ? $activity->metadata : [];
        $object = $this->describeObject($metadata);
        $sourceLabel = $this->resolveSourceLabel($metadata);

        return match ($activity->eventType) {
            'click' => $this->buildClickSentence($displayName, $metadata, $object),
            'open' => $this->buildOpenSentence($displayName, $object, $sourceLabel),
            'activity_session' => $sourceLabel ? \sprintf('%s était actif sur %s', $displayName, $sourceLabel) : null,
            default => null,
        };
    }

    private function buildClickSentence(string $displayName, array $metadata, ?string $object): ?string
    {
        $buttonRaw = $this->stringValue($metadata, 'button_name');
        $button = $buttonRaw ? (AdherentActivityLabels::BUTTON_NAMES[$buttonRaw] ?? $buttonRaw) : null;

        if ($button && $object) {
            return \sprintf('%s a cliqué sur "%s" depuis %s', $displayName, $button, $object);
        }
        if ($button) {
            return \sprintf('%s a cliqué sur "%s"', $displayName, $button);
        }
        if ($object) {
            return \sprintf('%s a cliqué sur %s', $displayName, $object);
        }

        return null;
    }

    private function buildOpenSentence(string $displayName, ?string $object, ?string $sourceLabel): ?string
    {
        if ($object && $sourceLabel) {
            return \sprintf('%s a ouvert %s (%s)', $displayName, $object, $sourceLabel);
        }
        if ($object) {
            return \sprintf('%s a ouvert %s', $displayName, $object);
        }
        if ($sourceLabel) {
            return \sprintf('%s a ouvert une page (%s)', $displayName, $sourceLabel);
        }

        return null;
    }

    private function describeObject(array $metadata): ?string
    {
        $type = $this->stringValue($metadata, 'object_type');
        $name = $this->stringValue($metadata, 'object_name');
        $typeMeta = $type ? (AdherentActivityLabels::OBJECT_TYPES[$type] ?? null) : null;

        if ($name) {
            return $typeMeta ? \sprintf('%s %s "%s"', $typeMeta['article'], $typeMeta['label'], $name) : \sprintf('"%s"', $name);
        }

        return $typeMeta ? \sprintf('%s %s', $typeMeta['article'], $typeMeta['label']) : null;
    }

    private function resolveSourceLabel(array $metadata): ?string
    {
        $source = $this->stringValue($metadata, 'source');
        if (!$source) {
            return null;
        }

        return AdherentActivityLabels::METADATA_SOURCES[$source] ?? $source;
    }

    private function stringValue(array $metadata, string $key): ?string
    {
        $value = $metadata[$key] ?? null;

        return \is_string($value) && '' !== $value ? $value : null;
    }
}
