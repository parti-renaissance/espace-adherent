<?php

namespace App\Form\Procuration;

use App\Form\GenderType;
use App\Form\UnitedNationsCountryType;
use App\Procuration\ElectionContext;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractProcurationType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $years = range(date('Y') - 17, date('Y') - 120);

        $resolver
            ->setDefaults([
                'translation_domain' => false,
                'years' => array_combine($years, $years),
            ])
            ->setRequired('election_context')
            ->setAllowedTypes('election_context', ElectionContext::class)
        ;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('gender', GenderType::class)
            ->add('lastName', TextType::class, [
                'filter_emojis' => true,
            ])
            ->add('firstNames', TextType::class, [
                'filter_emojis' => true,
            ])
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
                'filter_emojis' => true,
            ])
            ->add('address', TextType::class, [
                'filter_emojis' => true,
            ])
            ->add('state', TextType::class, [
                'required' => false,
                'filter_emojis' => true,
            ])
            ->add('phone', PhoneNumberType::class, [
                'required' => false,
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
            ])
            ->add('emailAddress', EmailType::class)
            ->add('birthdate', BirthdayType::class, [
                'widget' => 'choice',
                'years' => $options['years'],
                'placeholder' => [
                    'year' => 'AAAA',
                    'month' => 'MM',
                    'day' => 'JJ',
                ],
            ])
        ;
    }
}
