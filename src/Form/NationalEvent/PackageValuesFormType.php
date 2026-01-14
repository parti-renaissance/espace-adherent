<?php

declare(strict_types=1);

namespace App\Form\NationalEvent;

use App\Form\NationalEvent\PackageField\AbstractFieldFormType;
use App\Form\NationalEvent\PackageField\AccommodationFieldFormType;
use App\Form\NationalEvent\PackageField\PlaceChoiceFieldFormType;
use App\Form\NationalEvent\PackageField\RadioFieldFormType;
use App\Form\NationalEvent\PackageField\SelectFieldFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PackageValuesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $packageConfig = $options['package_config'] ?? [];
        $reservedPlaces = $options['reserved_places'] ?? [];
        $currentValues = $options['current_values'] ?? [];

        foreach ($packageConfig as $fieldConfig) {
            $fieldType = $fieldConfig['type'] ?? null;
            $fieldId = $fieldConfig['cle'];

            $optionToPropagate = [
                'required' => false,
                'reserved_places' => $reservedPlaces[$fieldId] ?? [],
                'current_value' => $currentValue = $currentValues[$fieldId] ?? null,
                'from_admin' => $options['from_admin'] ?? false,
            ];

            /** @var AbstractFieldFormType|string $fieldTypeClass */
            $fieldTypeClass = match ($fieldType) {
                AccommodationFieldFormType::FIELD_NAME => AccommodationFieldFormType::class,
                PlaceChoiceFieldFormType::FIELD_NAME => PlaceChoiceFieldFormType::class,
                SelectFieldFormType::FIELD_NAME => SelectFieldFormType::class,
                default => RadioFieldFormType::class,
            };

            $builder->add($fieldId, $fieldTypeClass, array_merge($optionToPropagate, $fieldTypeClass::getFieldOptions($fieldConfig, $reservedPlaces, $currentValue)));
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'current_values' => [],
            ])
            ->setDefined(['package_config', 'reserved_places', 'from_admin', 'current_values'])
            ->addAllowedTypes('package_config', 'array')
            ->addAllowedTypes('current_values', 'array')
            ->addAllowedTypes('reserved_places', 'array')
            ->addAllowedTypes('from_admin', 'bool')
        ;
    }
}
