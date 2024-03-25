<?php

namespace App\Admin\Procuration;

use App\Form\Admin\Procuration\InitialRequestTypeEnumType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;

class ProcurationRequestAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept('list');
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('email', null, [
                'label' => 'Adresse email',
            ])
            ->add('type', null, [
                'label' => 'Type',
                'template' => 'admin/procuration_v2/_list_initial_request_type.html.twig',
            ])
            ->add('createdAt', null, [
                'label' => 'Date',
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('email', null, [
                'label' => 'Adresse email',
                'show_filter' => true,
            ])
            ->add('type', ChoiceFilter::class, [
                'label' => 'Type',
                'show_filter' => true,
                'field_type' => InitialRequestTypeEnumType::class,
                'field_options' => [
                    'multiple' => true,
                ],
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Date de création',
                'field_type' => DateRangePickerType::class,
                'show_filter' => true,
            ])
        ;
    }
}
