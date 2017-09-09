<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\{DatagridMapper, ListMapper};
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\{TextareaType, TextType};

class InviteAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    protected function configureShowFields(ShowMapper $show)
        : void
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
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
        : void
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
        : void
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
