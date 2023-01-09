<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;

class EmailAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('create');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('uuid', null, [
                'label' => 'UUID',
            ])
            ->add('messageClass', null, [
                'label' => 'Type du message',
            ])
            ->add('sender', null, [
                'label' => 'Expéditeur',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
                'format' => 'Y-m-d H:i:s',
            ])
            ->add('deliveredAt', null, [
                'label' => 'Date de traitement',
                'format' => 'Y-m-d H:i:s',
            ])
            ->add('recipientsAsString', null, [
                'label' => 'Destinataires',
            ])
            ->add('requestPayload', null, [
                'label' => 'Requête',
                'template' => 'admin/mailer/show_request.html.twig',
            ])
            ->add('responsePayload', null, [
                'label' => 'Réponse',
                'template' => 'admin/mailer/show_response.html.twig',
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('uuid', null, [
                'label' => 'UUID',
                'show_filter' => true,
            ])
            ->add('messageClass', null, [
                'label' => 'Type du message',
            ])
            ->add('sender', null, [
                'label' => 'Expéditeur',
                'show_filter' => true,
            ])
            ->add('recipients', null, [
                'label' => 'Destinataire',
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('uuid', null, [
                'label' => 'UUID',
            ])
            ->add('messageClass', null, [
                'label' => 'Type du message',
            ])
            ->add('sender', null, [
                'label' => 'Expéditeur',
            ])
            ->add('recipientsAsString', null, [
                'label' => 'Destinataires',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
                'format' => 'Y-m-d H:i:s',
            ])
            ->add('deliveredAt', null, [
                'label' => 'Date de traitement',
                'format' => 'Y-m-d H:i:s',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'show' => [],
                ],
            ])
        ;
    }
}
