<?php

namespace App\Form\ManagedUsers;

use App\Form\GenderType;
use App\Form\ZoneAutoCompleteType;
use App\ManagedUsers\ManagedUsersFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractManagedUsersFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('gender', GenderType::class, [
                'placeholder' => 'Tous',
                'expanded' => true,
                'required' => false,
            ])
            ->add('ageMin', IntegerType::class, ['required' => false])
            ->add('ageMax', IntegerType::class, ['required' => false])
            ->add('firstName', TextType::class, ['required' => false])
            ->add('lastName', TextType::class, ['required' => false])
            ->add('zones', ZoneAutoCompleteType::class, [
                'remote_params' => ['space' => $options['space_type']],
            ])
            ->add('emailSubscription', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'common.all' => null,
                    'common.adherent.subscribed' => true,
                    'common.adherent.unsubscribed' => false,
                ],
                'choice_value' => function ($choice) {
                    return false === $choice ? '0' : (string) $choice;
                },
            ])
            ->add('sort', HiddenType::class, ['required' => false])
            ->add('order', HiddenType::class, ['required' => false])
        ;
    }

    public function getBlockPrefix()
    {
        return 'f';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => ManagedUsersFilter::class,
                'allow_extra_fields' => true,
                'space_type' => null,
            ])
            ->setAllowedTypes('space_type', 'string')
        ;
    }
}
