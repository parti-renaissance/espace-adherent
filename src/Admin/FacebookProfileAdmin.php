<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class FacebookProfileAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 64,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->add('facebookId', null, [
                'label' => 'ID Facebook',
            ])
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('emailAddress', null, [
                'label' => 'Email',
            ])
            ->add('gender', null, [
                'label' => 'Sexe',
            ])
            ->add('ageRangeAsString', null, [
                'label' => 'Age',
            ])
            ->add('createdAt', null, [
                'label' => 'Date',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'show' => [],
                ],
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->add('facebookId', null, [
                'label' => 'ID Facebook',
            ])
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('emailAddress', null, [
                'label' => 'Email',
            ])
            ->add('gender', null, [
                'label' => 'Sexe',
            ])
            ->add('ageRangeAsString', null, [
                'label' => 'Age',
            ])
            ->add('createdAt', null, [
                'label' => 'Date',
            ])
        ;
    }
}
