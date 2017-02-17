<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Sonata\AdminBundle\Show\ShowMapper;

class JeMarcheReportAdmin extends AbstractAdmin
{
    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('type', null, [
                'label' => 'Type',
            ])
            ->add('emailAddress', null, [
                'label' => 'Organisateur',
            ])
            ->add('convincedAsString', null, [
                'label' => 'Convaincus',
            ])
            ->add('almostConvincedAsString', null, [
                'label' => 'Quasi-convaincus',
            ])
            ->add('notConvinced', null, [
                'label' => 'Non convaincus',
            ])
            ->add('reaction', null, [
                'label' => 'Réaction globale',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('type', null, [
                'label' => 'Type',
            ])
            ->add('emailAddress', null, [
                'label' => 'Organisateur',
            ]);
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('type', null, [
                'label' => 'Type',
            ])
            ->add('emailAddress', null, [
                'label' => 'Organisateur',
            ])
            ->add('countConvinced', NumberType::class, [
                'label' => 'Convaincus',
            ])
            ->add('countAlmostConvinced', NumberType::class, [
                'label' => 'Quasi-convaincus',
            ])
            ->add('notConvinced', NumberType::class, [
                'label' => 'Non convaincus',
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
