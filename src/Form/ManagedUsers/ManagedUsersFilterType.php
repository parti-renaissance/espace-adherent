<?php

namespace App\Form\ManagedUsers;

use App\Form\DatePickerType;
use App\Form\EventListener\IncludeExcludeFilterRoleListener;
use App\Form\FilterRoleType;
use App\Form\MemberInterestsChoiceType;
use App\ManagedUsers\ManagedUsersFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ManagedUsersFilterType extends AbstractManagedUsersFilterType
{
    /** @var IncludeExcludeFilterRoleListener */
    private $includeExcludeFilterRoleListener;

    public function __construct(IncludeExcludeFilterRoleListener $includeExcludeFilterRoleListener)
    {
        $this->includeExcludeFilterRoleListener = $includeExcludeFilterRoleListener;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('isCommitteeMember', ChoiceType::class, [
                'required' => false,
                'expanded' => true,
                'choices' => [
                    'common.all' => null,
                    'global.yes' => true,
                    'global.no' => false,
                ],
                'choice_value' => function ($choice) {
                    return false === $choice ? '0' : (string) $choice;
                },
            ])
            ->add('includeRoles', FilterRoleType::class, ['required' => false])
            ->add('excludeRoles', FilterRoleType::class, ['required' => false])
            ->add('interests', MemberInterestsChoiceType::class, ['required' => false, 'expanded' => false])
            ->add('registeredSince', DatePickerType::class, ['required' => false])
            ->add('registeredUntil', DatePickerType::class, ['required' => false])
            ->add('voteInCommittee', ChoiceType::class, [
                'required' => false,
                'expanded' => true,
                'choices' => [
                    'common.all' => null,
                    'global.yes' => true,
                    'global.no' => false,
                ],
                'choice_value' => function ($choice) {
                    return false === $choice ? '0' : (string) $choice;
                },
            ])
        ;

        $builder->addEventSubscriber($this->includeExcludeFilterRoleListener);
    }

    public function configureOptions(OptionsResolver $resolver)
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
