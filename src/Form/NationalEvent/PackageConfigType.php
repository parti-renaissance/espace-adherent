<?php

declare(strict_types=1);

namespace App\Form\NationalEvent;

use App\NationalEvent\DTO\InscriptionRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PackageConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $reservedPlaces = $options['reserved_places'] ?? [];

        $defaultOptions = [
            'class' => 'rounded-lg p-6 border-2 hover:bg-white',
            'label_attr' => ['class' => 'grow shrink basis-0 text-gray-700'],
            'widget_side' => 'right',
        ];

        $builder
            ->add('withDiscount', CheckboxType::class, ['required' => false])
            ->add('roommateIdentifier', TextType::class, ['required' => false])
        ;

        foreach ($options['package_config'] as $fieldConfig) {
            $choices = $this->generateChoices($fieldConfig['options'] ?? []);

            $builder->add($fieldId = $fieldConfig['cle'], ChoiceType::class, [
                'label' => $fieldConfig['titre'] ?? null,
                'choices' => array_keys($choices),
                'attr' => [
                    'data-field-name' => $fieldId,
                    'placeholder' => $fieldConfig['placeholder'] ?? null,
                    'description' => $fieldConfig['description'] ?? null,
                ],
                'placeholder' => $fieldConfig['placeholder'] ?? null,
                'expanded' => \is_array($fieldConfig['options'][0] ?? []),
                'choice_value' => function ($choice) use ($choices) {
                    return $choices[$choice]['id'] ?? $choice;
                },
                'choice_label' => function ($choice) use ($choices) {
                    return $choices[$choice]['titre'] ?? $choice;
                },
                'choice_attr' => function ($key) use ($defaultOptions, $fieldConfig, $fieldId, $reservedPlaces): array {
                    $options = [
                        'data-field-name' => $fieldId,
                        ':class' => \sprintf('packageValues.'.$fieldId." === '%s' ? 'border-ui_blue-50 bg-white' : 'bg-ui_gray-1 border-ui_gray-1 hover:border-ui_gray-20'", $key),
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
                                        $defaultOptions['class'] .= ' opacity-60';
                                    }
                                }
                                $descriptionParts[] = $price.$quota;
                            }

                            $options['description'] = '<div>'.implode('</div><div>', array_filter($descriptionParts)).'</div>';
                            break;
                        }
                    }

                    return array_merge($defaultOptions, $options);
                },
            ]);
        }

        $builder->get('roommateIdentifier')->addModelTransformer(new CallbackTransformer(
            fn ($value) => $value,
            function ($value) {
                if (preg_match('/^\d{6}$/', (string) $value)) {
                    return substr($value, 0, 3).'-'.substr($value, 3);
                }

                return $value;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(['data_class' => InscriptionRequest::class])
            ->setDefined(['package_config', 'reserved_places'])
            ->addAllowedTypes('package_config', ['array'])
            ->addAllowedTypes('reserved_places', ['array'])
        ;
    }

    private function generateChoices(array $optionsConfig): array
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
