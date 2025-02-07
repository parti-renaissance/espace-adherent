<?php

namespace App\Form\BesoinDEurope;

use App\Form\AutocompleteAddressType;
use App\Form\GenderCivilityType;
use App\Form\ReCountryType;
use App\Form\RequiredCheckboxType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class DonationRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', HiddenType::class)
            ->add('email', EmailType::class)
            ->add('civility', GenderCivilityType::class)
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('nationality', ReCountryType::class)
            ->add('address', AutocompleteAddressType::class, ['with_additional_address' => true])
            ->add('autorisations', RequiredCheckboxType::class)
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'donation_request';
    }
}
