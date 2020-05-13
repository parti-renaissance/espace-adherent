<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class InviteAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'filter_emojis' => true,
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'filter_emojis' => true,
            ])
            ->add('email', null, [
                'label' => 'E-mail de l\'invité',
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'filter_emojis' => true,
            ])
            ->add('clientIp', null, [
                'label' => 'IP du client',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
        ;
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
            ])
        ;
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
            ])
        ;
    }
}
