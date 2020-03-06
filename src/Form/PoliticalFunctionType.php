<?php

namespace AppBundle\Form;

use AppBundle\Entity\ElectedRepresentative\PoliticalFunction;
use AppBundle\Entity\ElectedRepresentative\PoliticalFunctionNameEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PoliticalFunctionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', ChoiceType::class, [
                'placeholder' => '--',
                'choices' => PoliticalFunctionNameEnum::CHOICES,
            ])
            ->add('clarification', TextType::class, [
                'required' => false,
            ])
            ->add('onGoing', CheckboxType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('beginAt', 'sonata_type_date_picker')
            ->add('finishAt', 'sonata_type_date_picker', [
                'required' => false,
            ])
            ->add('geographicalArea', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PoliticalFunction::class,
        ]);
    }
}
