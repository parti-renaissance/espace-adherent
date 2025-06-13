<?php

namespace App\Form\NationalEvent;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CampusEventInscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $days = $options['transport_configuration']['jours'] ?? [];
        $transports = $options['transport_configuration']['transports'] ?? [];

        $defaultOptions = [
            'class' => ' rounded-lg p-6 border-2  hover:bg-white',
            'x-model' => 'selected',
            'label_attr' => ['class' => 'grow shrink basis-0 text-gray-700'],
            'widget_side' => 'right',
        ];

        $classPattern = "selected === '%s' ? 'border-ui_blue-50 bg-white' : 'bg-ui_gray-1 border-ui_gray-1 hover:border-ui_gray-20'";

        $builder
            ->add('accessibility', TextareaType::class, ['required' => false])
            ->add('visitDay', ChoiceType::class, [
                'choices' => array_column($days, 'id', 'titre'),
                'expanded' => true,
                'choice_attr' => function ($key) use ($defaultOptions, $classPattern, $days): array {
                    $options = [':class' => \sprintf($classPattern, $key)];

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
                'choice_attr' => function ($key) use ($defaultOptions, $classPattern, $transports): array {
                    $options = [':class' => \sprintf($classPattern, $key)];

                    foreach ($transports as $transport) {
                        if ($transport['id'] === $key) {
                            $descriptionParts = [$transport['description'] ?? null];

                            $price = '<span class="text-ui_blue-60 font-semibold">'.(!empty($transport['montant']) ? ($transport['montant'].' €') : 'Gratuit').'</span>';
                            $quota = !empty($transport['quota']) ? \sprintf('<span class="text-ui_gray-60"> - %d place%2$s restante%2$s</span>', $transport['quota'], $transport['quota'] > 1 ? 's' : '') : null;

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

    public function getParent(): string
    {
        return CommonEventInscriptionType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined('transport_configuration');
    }
}
