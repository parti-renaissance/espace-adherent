<?php

declare(strict_types=1);

namespace App\Admin\Email;

use App\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

class EmailSenderAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list', 'create', 'edit', 'delete']);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name', null, ['label' => 'Nom', 'show_filter' => true])
            ->add('email', null, ['label' => 'Email', 'show_filter' => true])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('name', null, ['label' => 'Nom'])
            ->add('email', null, ['label' => 'Email'])
            ->add(ListMapper::NAME_ACTIONS, null, [
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
            ->add('name', null, ['label' => 'Nom de l\'expéditeur'])
            ->add('email', null, [
                'label' => 'Email de l\'expéditeur',
                'help' => 'Doit appartenir à un domaine d\'envoi vérifié côté Mandrill, sinon les emails ne partiront pas.',
            ])
        ;
    }
}
