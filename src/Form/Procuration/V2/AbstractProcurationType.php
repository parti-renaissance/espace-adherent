<?php

namespace App\Form\Procuration\V2;

use App\Address\AddressInterface;
use App\Form\AcceptPersonalDataCollectType;
use App\Form\AutocompleteAddressType;
use App\Form\BirthdateType;
use App\Form\CivilityType;
use App\Form\ZoneUuidType;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class AbstractProcurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class)
            ->add('acceptCgu', AcceptPersonalDataCollectType::class, [
                'mapped' => false,
            ])
            ->add('gender', CivilityType::class)
            ->add('firstNames', TextType::class)
            ->add('lastName', TextType::class)
            ->add('birthdate', BirthdateType::class)
            ->add('phone', PhoneNumberType::class, [
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                'preferred_country_choices' => [AddressInterface::FRANCE],
                'default_region' => AddressInterface::FRANCE,
                'country_display_type' => PhoneNumberType::DISPLAY_COUNTRY_SHORT,
            ])
            ->add('address', AutocompleteAddressType::class, [
                'with_additional_address' => true,
            ])
            ->add('distantVotePlace', CheckboxType::class, [
                'required' => false,
            ])
            ->add('voteZone', ZoneUuidType::class)
            ->add('votePlace', ZoneUuidType::class, [
                'required' => false,
                'error_bubbling' => false,
            ])
            ->add('customVotePlace', TextType::class, [
                'required' => false,
                'error_bubbling' => true,
            ])
            ->add('joinNewsletter', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }
}
