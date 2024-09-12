<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

class UserActionHistoryAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'date';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list']);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('adherent', null, [
                'label' => 'Adhérent',
                'template' => 'admin/user_action_history/list_adherent.html.twig',
            ])
            ->add('type', null, [
                'label' => 'Type',
                'template' => 'admin/user_action_history/list_type.html.twig',
            ])
            ->add('date', null, [
                'label' => 'Date',
            ])
            ->add('data', null, [
                'label' => 'Données',
            ])
            ->add('impersonificator', null, [
                'label' => 'Administrateur',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }
}
