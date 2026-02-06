<?php

declare(strict_types=1);

namespace App\Form;

use App\Donation\Request\DonationRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DonationRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', AmountType::class, [
                'error_bubbling' => false,
            ])
            ->add('duration', HiddenType::class, [
                'error_bubbling' => false,
            ])
            ->add('localDestination', CheckboxType::class)

            ->add('gender', GenderCivilityType::class)
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('emailAddress', EmailType::class)
            ->add('nationality', ReCountryType::class)
            ->add('address', AutocompleteAddressType::class, ['with_additional_address' => true])

            ->add('autorisations', RequiredCheckboxType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DonationRequest::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'donation_request';
    }
}
