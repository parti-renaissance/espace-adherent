<?php

namespace App\Form\Renaissance\Donation;

use App\Donation\DonationRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DonationRequestAmountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', HiddenType::class)
            ->add('duration', HiddenType::class)
            ->add('localDestination', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => DonationRequest::class,
                'validation_groups' => ['choose_donation_amount'],
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
