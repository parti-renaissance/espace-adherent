<?php

namespace AppBundle\Form\Jecoute;

use AppBundle\Entity\Jecoute\Choice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoiceFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', TextType::class, [
                'filter_emojis' => true,
                'label' => false,
            ])
            ->add('position', HiddenType::class, [
                'attr' => [
                    'class' => 'choices-position',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Choice::class);
    }
}
