<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class BecomeAdherentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('firstName')
            ->remove('lastName')
            ->remove('emailAddress')
            ->remove('position')
        ;
    }

    public function getParent()
    {
        return UpdateMembershipRequestType::class;
    }
}
