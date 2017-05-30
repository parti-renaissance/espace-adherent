<?php

namespace AppBundle\Form;

use AppBundle\Donation\PayboxPaymentSubscription;
use AppBundle\Validator\PayboxSubscription;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PayboxPaymentSubscriptionType extends AbstractType
{
    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'choices' => PayboxPaymentSubscription::DURATIONS,
                'expanded' => true,
                'constraints' => new PayboxSubscription(),
            ])
        ;
    }
}
