<?php

namespace App\Admin\LocalElection;

use App\Admin\AbstractAdmin;
use App\Form\CivilityType;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class CandidacyAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('gender', CivilityType::class, ['expanded' => false, 'label' => 'Civilité'])
            ->add('lastName', null, ['label' => 'Nom'])
            ->add('firstName', null, ['label' => 'Prénoms'])
            ->add('email', null, ['label' => 'Email'])
            ->add('position', IntegerType::class, ['label' => 'Position', 'attr' => ['min' => 1, 'step' => 1]])
        ;
    }

    public function toString($object): string
    {
        return $object->firstName.' '.$object->lastName;
    }
}
