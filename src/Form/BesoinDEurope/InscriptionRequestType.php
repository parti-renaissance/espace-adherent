<?php

namespace App\Form\BesoinDEurope;

use App\BesoinDEurope\Inscription\InscriptionRequest;
use App\Form\AutocompleteAddressType;
use App\Form\CivilityType;
use App\Form\RequiredCheckboxType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InscriptionRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class)
            ->add('civility', CivilityType::class)
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('address', AutocompleteAddressType::class, ['with_additional_address' => true])
            ->add('partyMembership', ChoiceType::class, [
                'expanded' => true,
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
            ])
            ->add('utmSource', HiddenType::class)
            ->add('utmCampaign', HiddenType::class)
            ->add('allowNotifications', CheckboxType::class, ['required' => false])
            ->add('acceptCgu', RequiredCheckboxType::class, ['mapped' => true])
            ->add('acceptCgu2', RequiredCheckboxType::class, ['mapped' => true])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InscriptionRequest::class,
        ]);
    }
}
