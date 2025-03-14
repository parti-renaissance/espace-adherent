<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;

class AdherentRequestAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list']);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('email', null, [
                'label' => 'Email',
                'show_filter' => true,
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Date',
                'field_type' => DateRangePickerType::class,
                'show_filter' => true,
            ])
            ->add('utmSource', null, [
                'label' => 'UTM Source',
                'show_filter' => true,
            ])
            ->add('utmCampaign', null, [
                'label' => 'UTM Campagne',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('email', null, [
                'label' => 'Email',
            ])
            ->add('createdAt', null, [
                'label' => 'Date',
            ])
            ->add('utmSource', null, [
                'label' => 'UTM Source',
            ])
            ->add('utmCampaign', null, [
                'label' => 'UTM Campagne',
            ])
        ;
    }
}
