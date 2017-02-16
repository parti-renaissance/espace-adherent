<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class InviteAdmin extends AbstractAdmin
{
    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('email', null, [
                'label' => 'E-mail de l\'invité',
            ])
            ->add('message', null, [
                'label' => 'Message',
            ])
            ->add('clientIp', null, [
                'label' => 'IP du client',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('email', null, [
                'label' => 'E-mail de l\'invité',
            ]);
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('email', null, [
                'label' => 'E-mail de l\'invité',
            ])
            ->add('clientIp', null, [
                'label' => 'IP du client',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'show' => [],
                    'delete' => [],
                ],
            ]);
    }
}
