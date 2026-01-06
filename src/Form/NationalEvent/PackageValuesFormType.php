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

        foreach ($packageConfig as $fieldConfig) {
            $fieldType = $fieldConfig['type'] ?? null;
            $fieldId = $fieldConfig['cle'];

            /** @var AbstractFieldFormType|string $fieldTypeClass */
            $fieldTypeClass = match ($fieldType) {
                AccommodationFieldFormType::FIELD_NAME => AccommodationFieldFormType::class,
                PlaceChoiceFieldFormType::FIELD_NAME => PlaceChoiceFieldFormType::class,
                SelectFieldFormType::FIELD_NAME => SelectFieldFormType::class,
                default => RadioFieldFormType::class,
            };

            $builder->add($fieldId, $fieldTypeClass, $fieldTypeClass::getFieldOptions($fieldConfig, $reservedPlaces));
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined(['package_config'])
            ->addAllowedTypes('package_config', 'array')
        ;
    }
}
