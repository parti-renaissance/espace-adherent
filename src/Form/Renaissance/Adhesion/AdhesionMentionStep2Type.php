<?php

namespace App\Form\Renaissance\Adhesion;

use App\Form\RequiredCheckboxType;
use App\Membership\MembershipRequest\RenaissanceMembershipRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdhesionMentionStep2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', AdhesionAmountType::class)
            ->add('isPhysicalPerson', RequiredCheckboxType::class)
            ->add('conditions', CheckboxType::class)
            ->add('cguAccepted', CheckboxType::class)
            ->add('allowEmailNotifications', CheckboxType::class, [
                'required' => false,
            ])
            ->add('allowMobileNotifications', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RenaissanceMembershipRequest::class,
            'validation_groups' => ['membership_request_amount'],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_renaissance_membership';
    }
}
