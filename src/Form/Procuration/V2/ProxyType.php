<?php

namespace App\Form\Procuration\V2;

use App\Procuration\V2\Command\ProxyCommand;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProxyType extends AbstractProcurationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('electorNumber', TextType::class)
            ->add('slots', ChoiceType::class, [
                'expanded' => true,
                'choices' => [
                    'procuration.slots.choice_1' => 1,
                    'procuration.slots.choice_2' => 2,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProxyCommand::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'procuration_proxy';
    }
}
