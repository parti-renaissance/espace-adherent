<?php

declare(strict_types=1);

namespace App\Admin\VotingPlatform\Designation\Poll;

use App\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Type\CollectionType;

class DesignationPollAdmin extends AbstractAdmin
{
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id')
            ->addIdentifier('label')
            ->add('createdAt')
            ->add(ListMapper::NAME_ACTIONS, ListMapper::TYPE_ACTIONS, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Général')
                ->add('label', null, ['label' => 'Libellé'])
            ->end()
            ->with('Questions')
                ->add('questions', CollectionType::class, [
                    'label' => false,
                    'by_reference' => false,
                    'error_bubbling' => false,
                    'btn_add' => 'Ajouter',
                ], [
                    'edit' => 'inline',
                    'inline' => 'table',
                ])
            ->end()
        ;
    }
}
