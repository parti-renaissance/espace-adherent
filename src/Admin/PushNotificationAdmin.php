<?php

declare(strict_types=1);

namespace App\Admin;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;

class PushNotificationAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list']);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('notificationClass', null, ['label' => 'Notification', 'show_filter' => true])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Date d\envoi',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('notificationClass', null, ['label' => 'Notification'])
            ->add('title', null, ['label' => 'Titre'])
            ->add('body', null, ['label' => 'Contenu'])
            ->add('data', null, ['label' => 'Data'])
            ->add('scope', null, ['label' => 'Scope'])
            ->add('tokensCount', null, ['label' => 'Nombre de tokens'])
            ->add('createdAt', null, ['label' => 'Date d\'envoi'])
        ;
    }
}
