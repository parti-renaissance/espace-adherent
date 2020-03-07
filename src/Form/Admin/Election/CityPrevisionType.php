<?php

namespace AppBundle\Form\Admin\Election;

use AppBundle\Entity\Election\CityPrevision;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CityPrevisionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('strategy', ChoiceType::class, [
                'label' => 'Stratégie',
                'choices' => CityPrevision::STRATEGY_CHOICES,
                'choice_label' => function (string $choice) {
                    return "election.city_prevision.$choice";
                },
                'placeholder' => 'Sélectionnez',
                'required' => false,
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'required' => false,
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'required' => false,
            ])
            ->add('alliances', TextType::class, [
                'label' => 'Alliances',
                'required' => false,
            ])
            ->add('allies', TextType::class, [
                'label' => 'Alliés',
                'required' => false,
            ])
            ->add('validatedBy', TextType::class, [
                'label' => 'Validé par',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', CityPrevision::class);
    }
}
