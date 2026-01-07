<?php

declare(strict_types=1);

namespace App\Form\NationalEvent\PackageField;

use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class PlaceChoiceFieldFormType extends AbstractFieldFormType
{
    public const FIELD_NAME = 'place';

    private const ROW_PLACES = [
        16, // A
        22, // B
        24, // C
        27, // D
        29, // E
        33, // F
        36, // G
        39, // H
        43, // I
        46, // J
        47, // K
        51, // L
        55, // M
        56, // N
        57, // O
        11, // P
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $reservedPlaces = $options['reserved_places'];

        $rows = $this->getRowChoices();
        $builder
            ->add('row', ChoiceType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Rangée',
                    'x-model' => 'selectedRow',
                ],
                'choices' => array_combine(array_map(fn (string $val) => 'Rangée '.$val, $rows), $rows),
            ])
            ->add('place', ChoiceType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Place',
                ],
                'choice_attr' => function ($place) use ($reservedPlaces) {
                    if (!\in_array($place, $reservedPlaces, true)) {
                        return [];
                    }

                    return [
                        'disabled' => true,
                        'suffix' => 'Réservée',
                    ];
                },
                'choices' => $this->getPlaceChoices($rows),
            ])
            ->addModelTransformer(new CallbackTransformer(function ($value) {
                if (empty($value)) {
                    return ['row' => null, 'place' => null];
                }

                $row = mb_substr($value, 0, 1);
                $place = mb_substr($value, 1);

                return ['row' => $row, 'place' => $row.$place];
            }, function ($value) {
                if (!\is_array($value) || !isset($value['place']) || '' === $value['place']) {
                    return null;
                }

                return $value['place'];
            }))
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['reserved_places'] = $options['reserved_places'];
    }

    public static function getFieldOptions(array $fieldConfig, array $reservedPlaces): array
    {
        $fieldId = $fieldConfig['cle'];

        return [
            'reserved_places' => array_merge(array_keys($reservedPlaces[$fieldId] ?? []), $fieldConfig['places_reservees'] ?? []),
            'error_bubbling' => false,
            'label' => $fieldConfig['titre'] ?? null,
            'attr' => [
                'data-field-name' => $fieldId,
                'class' => 'flex gap-4',
                'placeholder' => $fieldConfig['placeholder'] ?? null,
                'description' => $fieldConfig['description'] ?? null,
            ],
        ];
    }

    private function getRowChoices(): array
    {
        return array_map(static fn ($index) => \chr(65 + $index), array_keys(self::ROW_PLACES));
    }

    private function getPlaceChoices(array $rows): array
    {
        $places = [];

        foreach (self::ROW_PLACES as $index => $seatCount) {
            for ($i = 1; $i <= $seatCount; ++$i) {
                $places[] = $rows[$index].$i;
            }
        }

        return array_combine(array_map(static fn (string $place) => 'Place '.$place, $places), $places);
    }
}
