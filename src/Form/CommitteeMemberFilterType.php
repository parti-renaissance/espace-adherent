<?php

namespace App\Form;

use App\Committee\Filter\ListFilterObject;
use App\Renaissance\Membership\RenaissanceMembershipFilterEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitteeMemberFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ageMin', IntegerType::class, ['required' => false])
            ->add('ageMax', IntegerType::class, ['required' => false])
            ->add('firstName', TextType::class, ['required' => false])
            ->add('city', TextType::class, ['required' => false])
            ->add('registeredSince', DatePickerType::class, ['required' => false])
            ->add('registeredUntil', DatePickerType::class, ['required' => false])
            ->add('joinedSince', DatePickerType::class, ['required' => false])
            ->add('joinedUntil', DatePickerType::class, ['required' => false])
            ->add('sort', HiddenType::class, ['required' => false])
            ->add('order', HiddenType::class, ['required' => false])
            ->add('subscribed', ChoiceType::class, [
                'required' => false,
                'expanded' => true,
                'choices' => [
                    'common.all' => null,
                    'common.adherent.subscribed' => true,
                    'common.adherent.unsubscribed' => false,
                ],
                'choice_value' => function ($choice) {
                    return false === $choice ? '0' : (string) $choice;
                },
            ])
            ->add('gender', GenderType::class, [
                'placeholder' => 'Tous',
                'expanded' => true,
                'required' => false,
            ])
            ->add('renaissanceMembership', ChoiceType::class, [
                'required' => false,
                'choices' => RenaissanceMembershipFilterEnum::CHOICES,
                'placeholder' => 'Tous',
            ])
        ;

        if (true === $options['is_supervisor']) {
            $builder
                ->add('votersOnly', BooleanChoiceType::class)
                ->add('certified', BooleanChoiceType::class)
            ;
        }
    }

    public function getBlockPrefix(): string
    {
        return 'filter';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => ListFilterObject::class,
                'is_supervisor' => false,
            ])
            ->setAllowedTypes('is_supervisor', ['bool'])
        ;
    }
}
