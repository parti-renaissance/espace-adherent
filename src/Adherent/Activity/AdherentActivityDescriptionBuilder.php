<?php

declare(strict_types=1);

namespace App\Adherent\Activity;

class AdherentActivityDescriptionBuilder
{
    public function build(string $eventType, ?array $metadata): ?string
    {
        $metadata ??= [];

        return match ($eventType) {
            'delegated_access_add' => $this->buildDelegatedAccessAdd($metadata),
            'profile_update' => $this->buildProfileUpdate($metadata),
            'click' => $this->buildClick($metadata),
            'open' => $this->buildOpen($metadata),
            'activity_session' => $this->buildActivitySession($metadata),
            default => null,
        };
    }

    private function buildDelegatedAccessAdd(array $metadata): ?string
    {
        $actor = $this->stringValue($metadata, 'actor_name');
        $role = $this->stringValue($metadata, 'role');
        $zones = $metadata['zones'] ?? null;

        if (!$actor) {
            return null;
        }

        $zone = \is_array($zones) && isset($zones[0]) && \is_string($zones[0]) ? $zones[0] : null;

        if ($role && $zone) {
            return \sprintf('%s a ouvert un accès "%s" sur %s', $actor, $role, $zone);
        }

        if ($role) {
            return \sprintf('%s a ouvert un accès "%s"', $actor, $role);
        }

        return \sprintf('%s a ouvert un accès', $actor);
    }

    private function buildProfileUpdate(array $metadata): ?string
    {
        $labels = $metadata['modified_field_labels'] ?? null;

        if (!\is_array($labels) || empty($labels)) {
            return null;
        }

        $count = \count($labels);

        return match (true) {
            1 === $count => \sprintf('%s modifié', $labels[0]),
            2 === $count => \sprintf('%s et %s modifiés', $labels[0], $labels[1]),
            default => \sprintf('%s et %d autres modifiés', $labels[0], $count - 1),
        };
    }

    private function buildClick(array $metadata): ?string
    {
        $button = $this->stringValue($metadata, 'button_name');
        $object = $this->describeObject($metadata);

        $buttonLabel = $button ? (AdherentActivityLabels::BUTTON_NAMES[$button] ?? $button) : null;

        if ($buttonLabel && $object) {
            return \sprintf('A cliqué sur "%s" depuis %s', $buttonLabel, $object);
        }

        if ($buttonLabel) {
            return \sprintf('A cliqué sur "%s"', $buttonLabel);
        }

        if ($object) {
            return \sprintf('A cliqué sur %s', $object);
        }

        return null;
    }

    private function buildOpen(array $metadata): ?string
    {
        $object = $this->describeObject($metadata);
        $sourceLabel = $this->resolveSourceLabel($metadata);

        if ($object && $sourceLabel) {
            return \sprintf('A ouvert %s (%s)', $object, $sourceLabel);
        }

        if ($object) {
            return \sprintf('A ouvert %s', $object);
        }

        if ($sourceLabel) {
            return \sprintf('A ouvert une page (%s)', $sourceLabel);
        }

        return null;
    }

    private function buildActivitySession(array $metadata): ?string
    {
        $sourceLabel = $this->resolveSourceLabel($metadata);

        return $sourceLabel ? \sprintf('Était actif sur %s', $sourceLabel) : null;
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

        return $source ? (AdherentActivityLabels::METADATA_SOURCES[$source] ?? $source) : null;
    }

    private function stringValue(array $metadata, string $key): ?string
    {
        $value = $metadata[$key] ?? null;

        return \is_string($value) && '' !== $value ? $value : null;
    }
}
