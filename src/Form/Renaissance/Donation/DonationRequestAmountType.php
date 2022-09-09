<?php

namespace App\Form\Renaissance\Donation;

use App\Donation\DonationRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DonationRequestAmountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('amount', NumberType::class);

        $builder->add('donation_amount', SubmitType::class, ['label' => 'Je donne en ligne']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => DonationRequest::class,
                'validation_groups' => ['donation_request_amount'],
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'app_renaissance_donation';
    }
}
