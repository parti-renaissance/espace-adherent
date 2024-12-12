<?php

namespace App\Form\ManagedUsers;

use App\Form\BooleanChoiceType;
use App\Form\DatePickerType;
use App\Form\FilterRoleType;
use App\Form\MemberInterestsChoiceType;
use App\ManagedUsers\ManagedUsersFilter;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ManagedUsersFilterType extends AbstractManagedUsersFilterType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('isCommitteeMember', BooleanChoiceType::class)
            ->add('includeRoles', FilterRoleType::class, ['required' => false])
            ->add('excludeRoles', FilterRoleType::class, ['required' => false])
            ->add('interests', MemberInterestsChoiceType::class, ['required' => false, 'expanded' => false])
            ->add('registeredSince', DatePickerType::class, ['required' => false])
            ->add('registeredUntil', DatePickerType::class, ['required' => false])
            ->add('voteInCommittee', BooleanChoiceType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'data_class' => ManagedUsersFilter::class,
                'allow_extra_fields' => true,
            ])
        ;
    }
}
