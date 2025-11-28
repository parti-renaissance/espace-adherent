<?php

declare(strict_types=1);

namespace App\Admin\VotingPlatform\Designation\CandidacyPool;

use App\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class CandidaciesGroupAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('hidden', HiddenType::class, ['label' => false, 'mapped' => false])
            ->add('label', null, ['label' => 'Libellé'])
            ->add('candidacies', CollectionType::class, [
                'label' => false,
                'error_bubbling' => false,
                'by_reference' => false,
                'required' => false,
                'btn_add' => 'Candidat',
            ], [
                'edit' => 'inline',
                'inline' => 'table',
            ])
        ;
    }
}
