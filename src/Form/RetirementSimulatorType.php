<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RetirementSimulatorType extends AbstractType
{
    private const DELAY_BEFORE_RETIREMENT_CHOICES = [
        'before_2037',
        'in_or_after_2037',
    ];

    private const YEAR_OF_BIRTH_CHOICES = [
        'before_2004',
        'in_or_after_2004',
    ];

    private const NUMBER_OF_CHILDREN_CHOICES = [
        'zero',
        'one',
        'two',
        'three_or_more',
    ];

    private const PROFESSION_CHOICES = [
        'private_sector',
        'independant',
        'official',
        'hospital_staff',
        'researcher',
        'teacher',
        'security_force',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('delayBeforeRetirement', ChoiceType::class, [
                'required' => true,
                'choices' => self::DELAY_BEFORE_RETIREMENT_CHOICES,
                'choice_label' => function (?string $choice) {
                    return 'retirement_simulator.delay_before_retirement.'.$choice;
                },
            ])
            ->add('yearOfBirth', ChoiceType::class, [
                'required' => true,
                'choices' => self::YEAR_OF_BIRTH_CHOICES,
                'choice_label' => function (?string $choice) {
                    return 'retirement_simulator.year_of_birth.'.$choice;
                },
            ])
            ->add('numberOfChildren', ChoiceType::class, [
                'required' => true,
                'choices' => self::NUMBER_OF_CHILDREN_CHOICES,
                'choice_label' => function (?string $choice) {
                    return 'retirement_simulator.number_of_children.'.$choice;
                },
            ])
            ->add('profession', ChoiceType::class, [
                'required' => true,
                'choices' => self::PROFESSION_CHOICES,
                'choice_label' => function (?string $choice) {
                    return 'retirement_simulator.profession.'.$choice;
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_retirement_simulator';
    }
}
