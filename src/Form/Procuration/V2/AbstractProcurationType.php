<?php

namespace App\Form\Procuration\V2;

use App\Form\AcceptPersonalDataCollectType;
use App\Form\AutocompleteAddressType;
use App\Form\BirthdateType;
use App\Form\CivilityType;
use App\Form\ZoneUuidType;
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
            ->add('address', AutocompleteAddressType::class, [
                'with_additional_address' => true,
            ])
            ->add('distantVotePlace', CheckboxType::class, [
                'required' => false,
            ])
            ->add('voteZone', ZoneUuidType::class, [
                'required' => false,
                'error_bubbling' => true,
            ])
            ->add('votePlace', ZoneUuidType::class, [
                'required' => false,
                'error_bubbling' => true,
            ])
        ;
    }
}
