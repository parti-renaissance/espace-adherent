<?php

namespace App\Form\Renaissance\Donation;

use App\Donation\DonationRequest;
use App\Form\AcceptPersonalDataCollectType;
use App\Form\RequiredCheckboxType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DonationRequestMentionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isPhysicalPerson', RequiredCheckboxType::class)
            ->add('hasFrenchNationality', CheckboxType::class)
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

    public function getBlockPrefix()
    {
        return 'app_renaissance_donation';
    }
}
