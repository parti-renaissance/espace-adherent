<?php

declare(strict_types=1);

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

class VotePlaceAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'name';
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept('list');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('code', null, [
                'label' => 'Code',
            ])
            ->add('address', null, [
                'label' => 'Adresse postale',
            ])
            ->add('postalCode', null, [
                'label' => 'Code postal',
            ])
            ->add('city', null, [
                'label' => 'Ville',
            ])
            ->add('country', null, [
                'label' => 'Pays',
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('code', null, [
                'label' => 'Code',
            ])
            ->add('address', null, [
                'label' => 'Adresse postale',
            ])
            ->add('postalCode', null, [
                'label' => 'Code postal',
            ])
            ->add('city', null, [
                'label' => 'Ville',
            ])
        ;
    }
}
