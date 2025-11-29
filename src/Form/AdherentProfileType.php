<?php

declare(strict_types=1);

namespace App\Form;

use App\Address\AddressInterface;
use App\AdherentProfile\AdherentProfile;
use App\Entity\ActivityAreaEnum;
use App\Entity\JobEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdherentProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $adherent = $builder->getData();

        $countryCode = $adherent ? $adherent->getPostAddress()->getCountry() : null;

        $builder
            ->add('firstName', TextType::class, [
                'format_identity_case' => true,
                'disabled' => $options['disabled_form'],
            ])
            ->add('lastName', TextType::class, [
                'format_identity_case' => true,
                'disabled' => $options['disabled_form'],
            ])
            ->add('nationality', ReCountryType::class, [
                'placeholder' => 'Nationalité',
            ])
            ->add('emailAddress', EmailType::class)
            ->add('position', ActivityPositionType::class, [
                'required' => false,
                'placeholder' => 'common.i.am',
            ])
            ->add('gender', $options['is_renaissance'] ? GenderCivilityType::class : GenderType::class, [
                'disabled' => $options['disabled_form'],
                'expanded' => false,
            ])
        ;

        if ($options['is_renaissance']) {
            $builder
                ->add('postAddress', AutocompleteAddressType::class, [
                    'with_additional_address' => true,
                    'validation_groups' => ['fill_personal_info'],
                ])
            ;
        } else {
            $builder
                ->add('customGender', TextType::class, [
                    'required' => false,
                    'disabled' => $options['disabled_form'],
                ])
                ->add('postAddress', AddressType::class, [
                    'label' => false,
                    'child_error_bubbling' => false,
                ])
            ;
        }

        $builder
            ->add('birthdate', DatePickerType::class, [
                'disabled' => $options['disabled_form'],
                'max_date' => new \DateTime('-15 years'),
                'min_date' => new \DateTime('-120 years'),
            ])
            ->add('phone', TelNumberType::class, [
                'required' => false,
                'preferred_country_choices' => $countryCode ? [$countryCode] : [AddressInterface::FRANCE],
            ])
            ->add('facebookPageUrl', UrlType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'https://www.facebook.com/EmmanuelMacron'],
            ])
            ->add('twitterPageUrl', UrlType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'https://twitter.com/EmmanuelMacron'],
            ])
            ->add('linkedinPageUrl', UrlType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'https://fr.linkedin.com/in/EmmanuelMacron'],
            ])
            ->add('telegramPageUrl', UrlType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'https://t.me/EmmanuelMacron'],
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
            ->add('mandates', AdherentMandateType::class, [
                'required' => false,
                'multiple' => true,
                'expanded' => $options['is_renaissance'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined('disabled_form');
        $resolver->setAllowedTypes('disabled_form', 'bool');
        $resolver->setDefault('disabled_form', false);

        $resolver->setDefined('is_renaissance');
        $resolver->setAllowedTypes('is_renaissance', 'bool');
        $resolver->setDefault('is_renaissance', false);

        $resolver->setDefaults([
            'data_class' => AdherentProfile::class,
        ]);
    }
}
