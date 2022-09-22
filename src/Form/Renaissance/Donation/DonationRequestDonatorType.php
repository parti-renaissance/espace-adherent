<?php

namespace App\Form\Renaissance\Donation;

use App\Address\Address;
use App\Donation\DonationRequest;
use App\Form\AutocompleteAddressType;
use App\ValueObject\Genders;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DonationRequestDonatorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fromAdherent = $options['from_adherent'];

        $builder
            ->add('gender', ChoiceType::class, [
                'choices' => Genders::CIVILITY_CHOICES,
                'translation_domain' => 'messages',
                'disabled' => $fromAdherent,
                'expanded' => true,
                'placeholder' => '',
            ])
            ->add('firstName', TextType::class, [
                'format_identity_case' => true,
                'disabled' => $fromAdherent,
            ])
            ->add('lastName', TextType::class, [
                'format_identity_case' => true,
                'disabled' => $fromAdherent,
            ])
            ->add('emailAddress', EmailType::class, [
                'disabled' => $fromAdherent,
            ])
            ->add('nationality', CountryType::class, [
                'preferred_choices' => [Address::FRANCE],
                'placeholder' => '',
            ])
            ->add('address', AutocompleteAddressType::class)
        ;

        $builder->add('fill_personal_info', SubmitType::class, ['label' => 'Étape suivante']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => DonationRequest::class,
                'validation_groups' => ['fill_personal_info'],
                'from_adherent' => false,
            ])
            ->setAllowedTypes('from_adherent', 'bool')
        ;
    }

    public function getBlockPrefix()
    {
        return 'app_renaissance_donation';
    }
}
