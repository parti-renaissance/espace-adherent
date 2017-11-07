<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateMembershipRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('conditions')
            ->add('position', ActivityPositionType::class)
        ;
    }

    public function getParent()
    {
        return MembershipRequestType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('validation_groups', ['Default']);
    }
}
