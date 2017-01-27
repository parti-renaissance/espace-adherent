<?php

namespace AppBundle\Form;

use AppBundle\Membership\AdherentEmailSubscription;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Choice;

class AdherentEmailSubscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('emails_subscriptions', ChoiceType::class, [
            'choices' => AdherentEmailSubscription::SUBSCRIPTIONS,
            'constraints' => new All([
                'constraints' => [new Choice([
                    'choices' => AdherentEmailSubscription::SUBSCRIPTIONS,
                    'strict' => true,
                ])],
            ]),
            'expanded' => true,
            'multiple' => true,
        ]);
    }
}
