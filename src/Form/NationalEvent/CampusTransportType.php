<?php

namespace App\Form\NationalEvent;

use App\Event\Request\EventInscriptionRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CampusTransportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $days = $options['transport_configuration']['jours'] ?? [];
        $transports = $options['transport_configuration']['transports'] ?? [];
        $reservedPlaces = $options['reserved_places'] ?? [];

        $defaultOptions = [
            'class' => 'rounded-lg p-6 border-2 hover:bg-white',
            'label_attr' => ['class' => 'grow shrink basis-0 text-gray-700'],
            'widget_side' => 'right',
        ];

        $builder
            ->add('withDiscount', CheckboxType::class, ['required' => false])
            ->add('visitDay', ChoiceType::class, [
                'choices' => array_column($days, 'id', 'titre'),
                'expanded' => true,
                'choice_attr' => function ($key) use ($defaultOptions, $days): array {
                    $options = [
                        ':class' => \sprintf("visitDay === '%s' ? 'border-ui_blue-50 bg-white' : 'bg-ui_gray-1 border-ui_gray-1 hover:border-ui_gray-20'", $key),
                    ];

                    foreach ($days as $day) {
                        if ($day['id'] === $key) {
                            $options['description'] = $day['description'] ?? null;
                            break;
                        }
                    }

                    return array_merge($defaultOptions, $options);
                },
            ])
            ->add('transport', ChoiceType::class, [
                'choices' => array_column($transports, 'id', 'titre'),
                'expanded' => true,
                'choice_attr' => function ($key) use ($defaultOptions, $reservedPlaces, $transports): array {
                    $options = [
                        ':class' => \sprintf("transport === '%s' ? 'border-ui_blue-50 bg-white' : 'bg-ui_gray-1 border-ui_gray-1 hover:border-ui_gray-20'", $key),
                        'x-show' => 'availableTransports.some(t => t.id === \''.$key.'\')',
                    ];

                    foreach ($transports as $transport) {
                        if ($transport['id'] === $key) {
                            $descriptionParts = [$transport['description'] ?? null];

                            $price = '<span class="text-ui_blue-60 font-semibold">'.(!empty($transport['montant']) ? ($transport['montant'].' €') : 'Gratuit').'</span>';
                            $quota = null;

                            if (!empty($transport['quota'])) {
                                $reservedCount = $reservedPlaces[$key] ?? 0;
                                $availablePlaces = $transport['quota'] - $reservedCount;

                                if ($availablePlaces > 0) {
                                    $quota = \sprintf('<span class="text-ui_gray-60"> - %d place%2$s restante%2$s</span>', $availablePlaces, $availablePlaces > 1 ? 's' : '');
                                } else {
                                    $quota = '<span class="text-ui_gray-60"> - Complet</span>';
                                    $options['disabled'] = true;
                                    $defaultOptions['class'] .= ' opacity-60';
                                }
                            }

                            $descriptionParts[] = $price.$quota;

                            $options['description'] = '<div>'.implode('</div><div>', array_filter($descriptionParts)).'</div>';
                            break;
                        }
                    }

                    return array_merge($defaultOptions, $options);
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(['data_class' => EventInscriptionRequest::class])
            ->setDefined(['transport_configuration', 'reserved_places'])
            ->addAllowedTypes('transport_configuration', ['array'])
            ->addAllowedTypes('reserved_places', ['array'])
        ;
    }
}
