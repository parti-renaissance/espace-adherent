<?php

namespace App\Form\Renaissance\Adhesion;

use App\Membership\MembershipRequest\RenaissanceMembershipRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MembershipRequestAmountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('amount', NumberType::class);

        $builder->add('membership_amount', SubmitType::class, ['label' => 'Étape suivante']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RenaissanceMembershipRequest::class,
            'validation_groups' => ['membership_request_amount'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_renaissance_membership';
    }
}
