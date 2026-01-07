<?php

declare(strict_types=1);

namespace App\Form\NationalEvent\PackageField;

use Symfony\Component\Form\AbstractType;

abstract class AbstractFieldFormType extends AbstractType
{
    public static function getFieldOptions(array $fieldConfig, array $reservedPlaces): array
    {
        $fieldId = $fieldConfig['cle'];
        $choices = static::generateChoices($fieldConfig['options'] ?? []);

        return [
            'error_bubbling' => false,
            'label' => $fieldConfig['titre'] ?? null,
            'choices' => array_keys($choices),
            'attr' => [
                'data-field-name' => $fieldId,
                'placeholder' => $fieldConfig['placeholder'] ?? null,
                'description' => $fieldConfig['description'] ?? null,
            ],
            'placeholder' => $fieldConfig['placeholder'] ?? null,
            'choice_value' => function ($choice) use ($choices) {
                return $choices[$choice]['id'] ?? $choice;
            },
            'choice_label' => function ($choice) use ($choices) {
                return $choices[$choice]['titre'] ?? $choice;
            },
            'choice_attr' => function ($key) use ($fieldConfig, $fieldId, $reservedPlaces): array {
                $options = [
                    'class' => 'rounded-lg p-6 border-2 hover:bg-white',
                    ':class' => \sprintf('packageValues.'.$fieldId." === '%s' ? 'border-ui_blue-50 bg-white' : 'bg-ui_gray-1 border-ui_gray-1 hover:border-ui_gray-20'", $key),
                    'label_attr' => ['class' => 'grow shrink basis-0 text-gray-700'],
                    'widget_side' => 'right',
                    'data-field-name' => $fieldId,
                    'x-show' => '!availabilities.'.$fieldId.' || availabilities.'.$fieldId.'.some(i => { console.log(i); return (i.id ?? i.titre) === \''.$key.'\'})',
                    'x-model' => 'packageValues[\''.$fieldId.'\']',
                ];

                foreach ($fieldConfig['options'] as $item) {
                    if ((\is_string($item) ?: ($item['id'] ?? $item['titre'])) === $key) {
                        $descriptionParts = [$item['description'] ?? null];

                        if (\array_key_exists('montant', $item)) {
                            $price = '<span class="text-ui_blue-60 font-semibold">'.($item['montant'] > 0 ? ($item['montant'].' €') : 'Gratuit').'</span>';
                            $quota = null;

                            if (!empty($item['quota'])) {
                                $reservedCount = $reservedPlaces[$key] ?? 0;
                                $availablePlaces = $item['quota'] - $reservedCount;

                                if ($availablePlaces > 0) {
                                    if ($availablePlaces > 50) {
                                        $quota = '<span class="text-ui_gray-60"> - Places limitées</span>';
                                    } else {
                                        $quota = \sprintf('<span class="text-ui_gray-60"> - %d place%2$s restante%2$s</span>', $availablePlaces, $availablePlaces > 1 ? 's' : '');
                                    }
                                } else {
                                    $quota = '<span class="text-ui_gray-60"> - Complet</span>';
                                    $options['disabled'] = true;
                                    $options['class'] .= ' opacity-60';
                                }
                            }
                            $descriptionParts[] = $price.$quota;
                        }

                        $options['description'] = '<div>'.implode('</div><div>', array_filter($descriptionParts)).'</div>';
                        break;
                    }
                }

                return $options;
            },
        ];
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
