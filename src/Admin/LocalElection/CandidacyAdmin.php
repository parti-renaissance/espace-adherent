<?php

declare(strict_types=1);

namespace App\Admin\LocalElection;

use App\Admin\AbstractAdmin;
use App\Form\GenderCivilityType;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class CandidacyAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('gender', GenderCivilityType::class, ['expanded' => false, 'label' => 'Civilité'])
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
