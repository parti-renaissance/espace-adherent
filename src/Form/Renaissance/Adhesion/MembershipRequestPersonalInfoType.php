<?php

namespace App\Form\Renaissance\Adhesion;

use App\Address\Address;
use App\Form\AddressType;
use App\Form\BirthdateType;
use App\Form\GenderType;
use App\Form\RepeatedEmailType;
use App\Membership\MembershipRequest\RenaissanceMembershipRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MembershipRequestPersonalInfoType extends AbstractType
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
            ->add('nationality', CountryType::class, [
                'placeholder' => 'Nationalité',
                'preferred_choices' => [Address::FRANCE],
            ])
            ->add('gender', GenderType::class, [
                'disabled' => $fromCertifiedAdherent,
            ])
            ->add('customGender', TextType::class, [
                'required' => false,
                'disabled' => $fromCertifiedAdherent,
            ])
            ->add('birthdate', BirthdateType::class, [
                'disabled' => $fromCertifiedAdherent,
            ])
            ->add('address', AddressType::class, [
                'set_address_region' => true,
                'label' => false,
                'child_error_bubbling' => false,
            ])
            ->add('fill_personal_info', SubmitType::class, ['label' => 'Étape suivante'])
        ;

        if ($fromAdherent) {
            $builder->add('emailAddress', EmailType::class, ['disabled' => true]);
        } else {
            $builder
                ->add('emailAddress', RepeatedEmailType::class, [])
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
            ])
            ->setAllowedTypes('from_adherent', 'bool')
            ->setAllowedTypes('from_certified_adherent', 'bool')
        ;
    }

    public function getBlockPrefix()
    {
        return 'app_renaissance_membership';
    }
}
