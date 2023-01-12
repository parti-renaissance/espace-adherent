<?php

namespace App\Form\Admin\Adherent;

use App\Address\Address;
use App\Form\AddressType;
use App\Form\BirthdateType;
use App\Form\CivilityType;
use App\Renaissance\Membership\Admin\AdherentCreateCommand;
use App\Renaissance\Membership\Admin\CotisationAmountChoiceEnum;
use App\Renaissance\Membership\Admin\MembershipTypeEnum;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateRenaissanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('gender', CivilityType::class)
            ->add('firstName', TextType::class, [
                'format_identity_case' => true,
            ])
            ->add('lastName', TextType::class, [
                'format_identity_case' => true,
            ])
            ->add('nationality', CountryType::class, [
                'preferred_choices' => [Address::FRANCE],
                'invalid_message' => 'common.nationality.invalid',
            ])
            ->add('address', AddressType::class, [
                'label' => false,
                'child_error_bubbling' => false,
            ])
            ->add('email', EmailType::class)
            ->add('phone', PhoneNumberType::class, [
                'required' => false,
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                'country_options' => [
                    'preferred_choices' => [Address::FRANCE],
                    'invalid_message' => 'common.country.invalid',
                ],
            ])
            ->add('birthdate', BirthdateType::class)
            ->add('membershipType', ChoiceType::class, [
                'choices' => MembershipTypeEnum::CHOICES,
                'choice_label' => function (string $value): string {
                    return "membership.type.$value";
                },
                'expanded' => true,
                'invalid_message' => 'admin.adherent.renaissance.membership_type.invalid_choice',
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => AdherentCreateCommand::class,
                'validation_groups' => 'admin_adherent_renaissance_create',
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'adherent_create';
    }
}
