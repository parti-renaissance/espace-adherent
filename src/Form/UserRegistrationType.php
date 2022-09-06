<?php

namespace App\Form;

use App\Membership\MembershipRequest\PlatformMembershipRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'format_identity_case' => true,
            ])
            ->add('lastName', TextType::class, [
                'format_identity_case' => true,
            ])
            ->add('nationality', CountryType::class, [
                'placeholder' => 'Nationalité',
            ])
            ->add('address', AddressType::class)
            ->add('allowEmailNotifications', CheckboxType::class, [
                'required' => false,
            ])
            ->add('emailAddress', RepeatedEmailType::class)
            ->add('password', PasswordType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PlatformMembershipRequest::class,
            'validation_groups' => ['Registration'],
            'country_iso' => null,
        ]);
    }
}
