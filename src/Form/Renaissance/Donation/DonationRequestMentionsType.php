<?php

namespace App\Form\Renaissance\Donation;

use App\Donation\Request\DonationRequest;
use App\Form\AcceptPersonalDataCollectType;
use App\Form\RequiredCheckboxType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DonationRequestMentionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isPhysicalPerson', RequiredCheckboxType::class)
            ->add('hasFrenchNationality', RequiredCheckboxType::class)
            ->add('personalDataCollection', AcceptPersonalDataCollectType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => DonationRequest::class,
                'validation_groups' => ['Default', 'donation_request_mentions'],
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'app_renaissance_donation';
    }
}
