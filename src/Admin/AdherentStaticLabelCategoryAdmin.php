<?php

declare(strict_types=1);

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

class AdherentStaticLabelCategoryAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list', 'create', 'edit', 'delete']);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('code', null, [
                'label' => 'Code',
                'show_filter' => true,
            ])
            ->add('label', null, [
                'label' => 'Label',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('code', null, [
                'label' => 'Code',
            ])
            ->add('label', null, [
                'label' => 'Label',
            ])
            ->add('sync', null, [
                'label' => 'Synchro tags',
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

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Label statique', ['class' => 'col-md-6'])
                ->add('code', null, [
                    'label' => 'Code',
                ])
                ->add('label', null, [
                    'label' => 'Label',
                ])
                ->add('sync', null, [
                    'label' => 'Synchro vers les tags adhérents?',
                ])
            ->end()
        ;
    }
}
