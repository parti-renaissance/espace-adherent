<?php

namespace App\Form;

use App\Address\Address;
use App\AdherentProfile\AdherentProfile;
use App\Entity\ActivityAreaEnum;
use App\Entity\JobEnum;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdherentProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $adherent = $builder->getData();

        $countryCode = $adherent ? $adherent->getAddress()->getCountry() : null;

        $builder
            ->add('firstName', TextType::class, [
                'format_identity_case' => true,
                'disabled' => $options['disabled_form'],
            ])
            ->add('lastName', TextType::class, [
                'format_identity_case' => true,
                'disabled' => $options['disabled_form'],
            ])
            ->add('nationality', CountryType::class, [
                'placeholder' => 'Nationalité',
                'preferred_choices' => [Address::FRANCE],
            ])
            ->add('emailAddress', EmailType::class)
            ->add('position', ActivityPositionType::class, [
                'required' => false,
                'placeholder' => 'common.i.am',
            ])
            ->add('gender', GenderType::class, [
                'disabled' => $options['disabled_form'],
            ])
            ->add('customGender', TextType::class, [
                'required' => false,
                'disabled' => $options['disabled_form'],
            ])
            ->add('birthdate', BirthdayType::class, [
                'widget' => 'single_text',
                'disabled' => $options['disabled_form'],
                'placeholder' => [
                    'year' => 'AAAA',
                    'month' => 'MM',
                    'day' => 'JJ',
                ],
            ])
            ->add('address', AddressType::class, [
                'label' => false,
                'child_error_bubbling' => false,
            ])
            ->add('phone', PhoneNumberType::class, [
                'required' => false,
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                'preferred_country_choices' => $countryCode ? [$countryCode] : [Address::FRANCE],
            ])
            ->add('facebookPageUrl', UrlType::class, [
                'required' => false,
            ])
            ->add('twitterPageUrl', UrlType::class, [
                'required' => false,
            ])
            ->add('linkedinPageUrl', UrlType::class, [
                'required' => false,
            ])
            ->add('telegramPageUrl', UrlType::class, [
                'required' => false,
            ])
            ->add('job', ChoiceType::class, [
                'choices' => JobEnum::JOBS,
                'choice_label' => function ($choice) {
                    return $choice;
                },
                'placeholder' => 'Mon métier',
                'required' => false,
            ])
            ->add('activityArea', ChoiceType::class, [
                'choices' => ActivityAreaEnum::ACTIVITIES,
                'choice_label' => function ($choice) {
                    return $choice;
                },
                'placeholder' => 'Mon secteur d\'activité',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined('disabled_form');
        $resolver->setAllowedTypes('disabled_form', 'bool');
        $resolver->setDefault('disabled_form', false);

        $resolver->setDefaults([
            'data_class' => AdherentProfile::class,
        ]);
    }
}
