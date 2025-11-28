<?php

declare(strict_types=1);

namespace App\Admin\VotingPlatform\Designation\CandidacyPool;

use App\Admin\AbstractAdmin;
use App\Form\GenderType;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class CandidacyAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('gender', GenderType::class, ['label' => 'Civilité'])
            ->add('firstName', null, ['label' => 'Prénom'])
            ->add('lastName', null, ['label' => 'Nom'])
            ->add('isSubstitute', null, ['label' => 'Suppléant'])
            ->add('position', IntegerType::class, ['label' => 'Position', 'attr' => ['min' => 1, 'step' => 1]])
        ;
    }
}
