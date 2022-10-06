<?php

namespace App\Form\Renaissance\Adhesion;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class MembershipRequestProceedPaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('payment', SubmitType::class, [
                'label' => 'Procéder au paiement',
            ])
        ;
    }
}
