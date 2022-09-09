<?php

namespace App\Form\Renaissance\Donation;

use App\Address\Address;
use App\Donation\DonationRequest;
use App\Form\UnitedNationsCountryType;
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
        $builder
            ->add('gender', ChoiceType::class, [
                'choices' => Genders::CIVILITY_CHOICES,
                'translation_domain' => 'messages',
                'expanded' => true,
            ])
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('emailAddress', EmailType::class)
            ->add('nationality', CountryType::class, [
                'preferred_choices' => [Address::FRANCE],
                'placeholder' => 'Nationalité',
            ])
            ->add('address', TextType::class)
            ->add('postalCode', TextType::class)
            ->add('cityName', TextType::class, [
                'required' => false,
            ])
            ->add('country', UnitedNationsCountryType::class, [
                'preferred_choices' => [Address::FRANCE],
            ])
        ;

        $builder->add('fill_personal_info', SubmitType::class, ['label' => 'Étape suivante']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => DonationRequest::class,
                'validation_groups' => ['fill_personal_info'],
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'app_renaissance_donation';
    }
}
