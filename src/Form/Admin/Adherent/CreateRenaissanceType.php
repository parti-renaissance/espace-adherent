<?php

namespace App\Form\Admin\Adherent;

use App\Address\AddressInterface;
use App\Form\AddressType;
use App\Form\BirthdateType;
use App\Form\GenderCivilityType;
use App\Form\ReCountryType;
use App\Form\TelNumberType;
use App\Renaissance\Membership\Admin\AdherentCreateCommand;
use App\Renaissance\Membership\Admin\CotisationAmountChoiceEnum;
use App\Renaissance\Membership\Admin\CotisationTypeChoiceEnum;
use App\Renaissance\Membership\Admin\MembershipTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateRenaissanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fromCertifiedAdherent = $options['from_certified_adherent'];

        $builder
            ->add('gender', GenderCivilityType::class, ['disabled' => $fromCertifiedAdherent])
            ->add('firstName', TextType::class, [
                'format_identity_case' => true,
                'disabled' => $fromCertifiedAdherent,
            ])
            ->add('lastName', TextType::class, [
                'format_identity_case' => true,
                'disabled' => $fromCertifiedAdherent,
            ])
            ->add('nationality', ReCountryType::class, [
                'disabled' => $fromCertifiedAdherent,
                'invalid_message' => 'common.nationality.invalid',
            ])
            ->add('address', AddressType::class, [
                'label' => false,
                'with_additional_address' => true,
                'child_error_bubbling' => false,
            ])
            ->add('email', EmailType::class, ['disabled' => true])
            ->add('phone', TelNumberType::class, [
                'required' => false,
                'country_options' => [
                    'preferred_choices' => [AddressInterface::FRANCE],
                    'invalid_message' => 'common.country.invalid',
                ],
            ])
            ->add('birthdate', BirthdateType::class, ['disabled' => $fromCertifiedAdherent])
            ->add('partyMembership', ChoiceType::class, [
                'choices' => MembershipTypeEnum::CHOICES,
                'choice_label' => function (string $value): string {
                    return "membership.type.$value";
                },
                'expanded' => true,
                'invalid_message' => 'admin.adherent.renaissance.membership_type.invalid_choice',
            ])

            ->add('cotisationTypeChoice', ChoiceType::class, [
                'choices' => CotisationTypeChoiceEnum::CHOICES,
                'choice_label' => function (string $value): string {
                    return "cotisation.type_choice.$value";
                },
                'expanded' => true,
            ])
            ->add('cotisationAmountChoice', ChoiceType::class, [
                'choices' => CotisationAmountChoiceEnum::CHOICES,
                'choice_label' => function (string $value): string {
                    return "cotistation.amount_choice.$value";
                },
                'expanded' => true,
                'invalid_message' => 'admin.membership.cotisation_amount_choice.invalid_choice',
            ])
            ->add('cotisationCustomAmount', NumberType::class, [
                'required' => false,
                'html5' => true,
            ])
            ->add('cotisationDate', DateType::class, [
                'years' => range(2022, date('Y')),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => AdherentCreateCommand::class,
                'validation_groups' => 'admin_adherent_renaissance_create',
                'from_certified_adherent' => false,
            ])
            ->setAllowedTypes('from_certified_adherent', 'bool')
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'adherent_create';
    }
}
