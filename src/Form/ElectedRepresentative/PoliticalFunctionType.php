<?php

namespace AppBundle\Form\ElectedRepresentative;

use AppBundle\Entity\ElectedRepresentative\Mandate;
use AppBundle\Entity\ElectedRepresentative\PoliticalFunction;
use AppBundle\Entity\ElectedRepresentative\PoliticalFunctionNameEnum;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
            ->add('mandate', EntityType::class, [
                'label' => false,
                'placeholder' => '--',
                'class' => Mandate::class,
                'choice_label' => 'number',
                'choices' => $options['mandates'],
            ])
            ->add('name', ChoiceType::class, [
                'label' => false,
                'placeholder' => '--',
                'choices' => PoliticalFunctionNameEnum::CHOICES,
            ])
            ->add('clarification', TextType::class, [
                'required' => false,
                'label' => false,
            ])
            ->add('onGoing', CheckboxType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('beginAt', 'sonata_type_date_picker', [
                'label' => false,
            ])
            ->add('finishAt', 'sonata_type_date_picker', [
                'label' => false,
                'required' => false,
                'error_bubbling' => false,
            ])
            ->add('mandateZoneName', TextType::class, [
                'label' => false,
                'disabled' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => PoliticalFunction::class,
                'mandates' => null,
            ])
            ->setAllowedTypes('mandates', 'array')
        ;
    }
}
