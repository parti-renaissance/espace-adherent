<?php

declare(strict_types=1);

namespace App\Admin\Reporting;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;

class DeclaredMandateHistoryAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'date';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept('list');
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('adherent.emailAddress', null, [
                'label' => 'Email adhérent',
                'show_filter' => true,
            ])
            ->add('administrator', null, [
                'label' => 'Administrateur',
                'show_filter' => true,
            ])
            ->add('date', DateRangeFilter::class, [
                'label' => 'Date',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('adherent', null, [
                'label' => 'Adhérent',
                'template' => 'admin/reporting/declared_mandate_history/list_adherent.html.twig',
            ])
            ->add('addedMandates', null, [
                'label' => 'Ajouté(s)',
                'template' => 'admin/reporting/declared_mandate_history/list_added_mandates.html.twig',
            ])
            ->add('removedMandates', null, [
                'label' => 'Supprimé(s)',
                'template' => 'admin/reporting/declared_mandate_history/list_removed_mandates.html.twig',
            ])
            ->add('date', null, [
                'label' => 'Date',
            ])
            ->add('administrator', null, [
                'label' => 'Administrateur',
            ])
        ;
    }
}
