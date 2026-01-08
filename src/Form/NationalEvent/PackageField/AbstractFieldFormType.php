<?php

declare(strict_types=1);

namespace App\Form\NationalEvent\PackageField;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractFieldFormType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined(['reserved_places'])
            ->addAllowedTypes('reserved_places', 'array')
        ;
    }

    public static function getFieldOptions(array $fieldConfig, array $reservedPlaces): array
    {
        $fieldId = $fieldConfig['cle'];
        $choices = static::generateChoices($fieldConfig['options'] ?? []);

        return [
            'reserved_places' => $reservedPlaces,
            'error_bubbling' => false,
            'label' => $fieldConfig['titre'] ?? null,
            'choices' => array_keys($choices),
            'attr' => [
                'data-field-name' => $fieldId,
                'placeholder' => $fieldConfig['placeholder'] ?? null,
                'description' => $fieldConfig['description'] ?? null,
            ],
            'placeholder' => $fieldConfig['placeholder'] ?? null,
            'choice_value' => static fn ($choice) => $choices[$choice]['id'] ?? $choice,
            'choice_label' => static fn ($choice) => $choices[$choice]['titre'] ?? $choice,
            'choice_attr' => function ($key) use ($fieldConfig, $fieldId, $reservedPlaces): array {
                $currentFieldReservations = $reservedPlaces[$fieldId] ?? [];

                $attributes = self::getBaseChoiceAttributes($key, $fieldId);

                $optionConfig = self::findOptionConfig($key, $fieldConfig['options']);

                if (!$optionConfig) {
                    return $attributes;
                }

                $attributes['description'] = self::buildDescriptionHtml(
                    $optionConfig,
                    $fieldConfig['options'],
                    $currentFieldReservations,
                    $attributes
                );

                return $attributes;
            },
        ];
    }

    private static function getBaseChoiceAttributes(string $key, string $fieldId): array
    {
        return [
            'class' => 'rounded-lg p-6 border-2 hover:bg-white',
            ':class' => \sprintf('packageValues.'.$fieldId." === '%s' ? 'border-ui_blue-50 bg-white' : 'bg-ui_gray-1 border-ui_gray-1 hover:border-ui_gray-20'", $key),
            'label_attr' => ['class' => 'grow shrink basis-0 text-gray-700'],
            'widget_side' => 'right',
            'data-field-name' => $fieldId,
            'x-show' => '!availabilities.'.$fieldId.' || availabilities.'.$fieldId.'.some(i => { return (i.id ?? i.titre) === \''.$key.'\'})',
            'x-model' => 'packageValues[\''.$fieldId.'\']',
        ];
    }

    private static function findOptionConfig(string $key, array $allOptions): ?array
    {
        foreach ($allOptions as $item) {
            $itemId = \is_string($item) ? $item : ($item['id'] ?? $item['titre']);
            if ($itemId === $key) {
                return \is_string($item) ? ['id' => $item, 'titre' => $item] : $item;
            }
        }

        return null;
    }

    private static function buildDescriptionHtml(array $item, array $allOptions, array $reservations, array &$attributes): string
    {
        $descriptionParts = [$item['description'] ?? null];

        if (\array_key_exists('montant', $item)) {
            $priceHtml = '<span class="text-ui_blue-60 font-semibold">'.($item['montant'] > 0 ? ($item['montant'].' €') : 'Gratuit').'</span>';
            $quotaHtml = null;

            if (\array_key_exists('quota', $item)) {
                $availablePlaces = self::calculateAvailablePlaces($item, $allOptions, $reservations);
                $quotaHtml = self::formatQuotaDisplay($availablePlaces, $attributes);
            }

            $descriptionParts[] = $priceHtml.$quotaHtml;
        }

        return '<div>'.implode('</div><div>', array_filter($descriptionParts)).'</div>';
    }

    private static function calculateAvailablePlaces(array $targetItem, array $allOptions, array $reservations): int
    {
        if (\is_array($targetItem['quota'])) {
            $dependenciesAvailability = [];

            foreach ($targetItem['quota'] as $depId) {
                $depConfig = self::findOptionConfig($depId, $allOptions);

                if ($depConfig) {
                    $depMax = $depConfig['quota'];
                    $depConsumed = self::getConsumedCount($depId, $allOptions, $reservations);

                    $dependenciesAvailability[] = $depMax - $depConsumed;
                }
            }

            return empty($dependenciesAvailability) ? 0 : min($dependenciesAvailability);
        }

        $max = $targetItem['quota'];
        $consumed = self::getConsumedCount($targetItem['id'] ?? $targetItem['titre'], $allOptions, $reservations);

        return $max - $consumed;
    }

    private static function getConsumedCount(string $targetId, array $allOptions, array $reservations): int
    {
        $count = $reservations[$targetId] ?? 0;

        foreach ($allOptions as $otherOption) {
            if (isset($otherOption['quota']) && \is_array($otherOption['quota'])) {
                if (\in_array($targetId, $otherOption['quota'], true)) {
                    $parentId = $otherOption['id'] ?? $otherOption['titre'];
                    $count += ($reservations[$parentId] ?? 0);
                }
            }
        }

        return $count;
    }

    private static function formatQuotaDisplay(int $availablePlaces, array &$attributes): string
    {
        if ($availablePlaces <= 0) {
            $attributes['disabled'] = true;
            $attributes['class'] .= ' opacity-60 pointer-events-none';

            return '<span class="text-ui_gray-60"> - Complet</span>';
        }

        if ($availablePlaces > 50) {
            return '<span class="text-ui_gray-60"> - Places limitées</span>';
        }

        return \sprintf(
            '<span class="text-ui_gray-60"> - %d place%s restante%s</span>',
            $availablePlaces,
            $availablePlaces > 1 ? 's' : '',
            $availablePlaces > 1 ? 's' : ''
        );
    }

    private static function generateChoices(array $optionsConfig): array
    {
        $choices = [];

        foreach ($optionsConfig as $option) {
            if (\is_string($option)) {
                $choices[$option] = ['id' => $option, 'titre' => $option];
                continue;
            }

            if (\is_array($option)) {
                $choices[$id = $option['id'] ?? $option['titre']] = [
                    'id' => $id,
                    'titre' => $option['titre'],
                ];
            }
        }

        return $choices;
    }
}
