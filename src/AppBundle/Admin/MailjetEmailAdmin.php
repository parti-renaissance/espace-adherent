<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class MailjetEmailAdmin extends AbstractAdmin
{
    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('subject', null, [
                'label' => 'Destinataire',
            ])
            ->add('recipient', null, [
                'label' => 'Destinataire',
            ])
            ->add('template', null, [
                'label' => 'Template',
            ])
            ->add('messageClass', null, [
                'label' => 'Type du message',
            ])
            ->add('messageBatchUuid', null, [
                'label' => 'ID de batch',
            ])
                ->add('delivered', 'boolean', [
                'label' => 'Delivered',
            ])
            ->add('sentAt', null, [
                'label' => 'Date d\'envoi',
            ])
            ->add('requestPayload', null, [
                'label' => 'Requête',
            ])
            ->add('responsePayload', null, [
                'label' => 'Réponse',
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('recipient', null, [
                'label' => 'Destinataire',
            ])
            ->add('messageClass', null, [
                'label' => 'Type du message',
            ]);
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('messageClass', null, [
                'label' => 'Type du message',
            ])
            ->add('recipient', null, [
                'label' => 'Destinataire',
            ])
            ->add('delivered', 'boolean', [
                'label' => 'Delivered',
            ])
            ->add('sentAt', null, [
                'label' => 'Date d\'envoi',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'show' => [],
                ],
            ]);
    }
}
