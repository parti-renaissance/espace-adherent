<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class EmailAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 128,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
    }

    protected function configureShowFields(ShowMapper $show)
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

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
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
            ])
            ->add('recipients', null, [
                'label' => 'Destinataire',
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
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
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'show' => [],
                ],
            ])
        ;
    }
}
