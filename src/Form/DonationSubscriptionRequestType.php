<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class DonationSubscriptionRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('duration', PayboxPaymentSubscriptionType::class)
            ->add('submit', SubmitType::class, [
                'label' => 'Continuer',
            ])
        ;
    }
}
