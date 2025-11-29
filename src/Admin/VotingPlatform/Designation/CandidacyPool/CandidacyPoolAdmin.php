<?php

declare(strict_types=1);

namespace App\Admin\VotingPlatform\Designation\CandidacyPool;

use App\Admin\AbstractAdmin;
use App\Entity\VotingPlatform\Designation\CandidacyPool\CandidacyPool;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Type\CollectionType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CandidacyPoolAdmin extends AbstractAdmin
{
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id')
            ->addIdentifier('label', null, ['label' => 'Libellé'])
            ->add('designation', null, ['label' => 'Désignation'])
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
            ->with('Libellé')
                ->add('label', null, ['label' => false])
            ->end()
            ->with('Listes de candidats')
                ->add('candidaciesGroups', CollectionType::class, [
                    'label' => false,
                    'by_reference' => false,
                    'error_bubbling' => false,
                    'required' => false,
                    'btn_add' => 'Liste',
                ], [
                    'edit' => 'inline',
                    'inline' => 'table',
                ])
            ->end()
        ;
    }

    /**
     * @param CandidacyPool $object
     */
    protected function preRemove(object $object): void
    {
        parent::preRemove($object);

        if (!$object->designation) {
            throw new AccessDeniedException();
        }
    }
}
