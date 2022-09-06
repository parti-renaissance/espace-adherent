<?php

namespace App\Form;

use App\Address\Address;
use App\Membership\MembershipRequest\PlatformMembershipRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdherentRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->remove('position')
            ->add('password', PasswordType::class)
            ->add('conditions', CheckboxType::class, [
                'required' => false,
            ])
            ->add('allowEmailNotifications', CheckboxType::class, [
                'required' => false,
            ])
            ->add('allowMobileNotifications', CheckboxType::class, [
                'required' => false,
            ])
            ->add('emailAddress', RepeatedEmailType::class)
            ->add('address', AddressType::class, [
                'set_address_region' => true,
                'label' => false,
                'child_error_bubbling' => false,
            ])
            ->add('nationality', CountryType::class, [
                'placeholder' => 'Nationalité',
                'preferred_choices' => [Address::FRANCE],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PlatformMembershipRequest::class,
            'validation_groups' => ['Update', 'Conditions', 'Registration'],
        ]);
    }

    public function getParent(): string
    {
        return AdherentType::class;
    }
}
