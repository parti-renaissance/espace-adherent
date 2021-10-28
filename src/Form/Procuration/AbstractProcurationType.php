<?php

namespace App\Form\Procuration;

use App\Form\DatePickerType;
use App\Form\GenderType;
use App\Form\UnitedNationsCountryType;
use App\Procuration\ElectionContext;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractProcurationType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'translation_domain' => false,
            ])
            ->setRequired('election_context')
            ->setAllowedTypes('election_context', ElectionContext::class)
        ;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('gender', GenderType::class)
            ->add('lastName', TextType::class)
            ->add('firstNames', TextType::class)
            ->add('country', UnitedNationsCountryType::class)
            ->add('postalCode', TextType::class, [
                'required' => false,
            ])
            ->add('city', HiddenType::class, [
                'required' => false,
                'error_bubbling' => true,
            ])
            ->add('cityName', TextType::class, [
                'required' => false,
            ])
            ->add('address', TextType::class)
            ->add('state', TextType::class, [
                'required' => false,
            ])
            ->add('phone', PhoneNumberType::class, [
                'required' => false,
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
            ])
            ->add('emailAddress', EmailType::class)
            ->add('birthdate', DatePickerType::class, [
                'max_date' => new \DateTime('-17 years'),
                'min_date' => new \DateTime('-120 years'),
            ])
        ;
    }
}
