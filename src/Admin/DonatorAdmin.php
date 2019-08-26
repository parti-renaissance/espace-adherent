<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DonatorAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 128,
        '_sort_order' => 'DESC',
        '_sort_by' => 'id',
    ];

    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'filter_emojis' => true,
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'filter_emojis' => true,
            ])
            ->add('emailAddress', null, [
                'label' => 'Adresse e-mail',
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('identifier', null, [
                'label', 'Numéro donateur',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('emailAddress', null, [
                'label' => 'Adresse e-mail',
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('identifier', null, [
                'label' => 'Numéro donateur',
            ])
            ->add('isAdherent', 'boolean', [
                'label' => 'Adhérent',
            ])
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('emailAddress', null, [
                'label' => 'Adresse e-mail',
            ])
            ->add('lastDonationAt', null, [
                'label' => 'Date du dernier don',
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
