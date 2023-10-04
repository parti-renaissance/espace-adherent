<?php

namespace App\Form\Renaissance\Adhesion;

use App\Form\AutocompleteAddressType;
use App\Form\RepeatedEmailType;
use App\Membership\MembershipRequest\RenaissanceMembershipRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonalInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fromCertifiedAdherent = $options['from_certified_adherent'];
        $fromAdherent = $options['from_adherent'] || $fromCertifiedAdherent;

        $builder
            ->add('firstName', TextType::class, [
                'format_identity_case' => true,
                'disabled' => $fromCertifiedAdherent,
            ])
            ->add('lastName', TextType::class, [
                'format_identity_case' => true,
                'disabled' => $fromCertifiedAdherent,
            ])
            ->add('address', AutocompleteAddressType::class)
        ;

        if ($fromAdherent) {
            $builder->add('emailAddress', EmailType::class, ['disabled' => true]);
        } else {
            $builder
                ->add('emailAddress', RepeatedEmailType::class, ['disable_duplicate' => $options['disable_duplicate']])
                ->add('password', PasswordType::class)
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => RenaissanceMembershipRequest::class,
                'validation_groups' => ['fill_personal_info'],
                'from_adherent' => false,
                'from_certified_adherent' => false,
                'disable_duplicate' => false,
            ])
            ->setAllowedTypes('from_adherent', 'bool')
            ->setAllowedTypes('from_certified_adherent', 'bool')
            ->setAllowedTypes('disable_duplicate', 'bool')
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'app_renaissance_membership';
    }
}
