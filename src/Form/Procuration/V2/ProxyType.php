<?php

declare(strict_types=1);

namespace App\Form\Procuration\V2;

use App\Procuration\V2\Command\ProxyCommand;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProxyType extends AbstractProcurationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('electorNumber', TextType::class, ['required' => false])
            ->add('slots', ChoiceType::class, [
                'expanded' => true,
                'choices' => [
                    'procuration.slots.choice_1' => 1,
                    'procuration.slots.choice_2' => 2,
                    'procuration.slots.choice_3' => 3,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => ProxyCommand::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'procuration_proxy';
    }
}
