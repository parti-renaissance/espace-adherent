<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class JeMarcheReportAdmin extends AbstractAdmin
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
            ->add('type', null, [
                'label' => 'Type',
            ])
            ->add('emailAddress', null, [
                'label' => 'Organisateur',
            ])
            ->add('postalCode', null, [
                'label' => 'Code postal',
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
            ->add('reaction', TextType::class, [
                'label' => 'Réaction globale',
                'filter_emojis' => true,
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('type', null, [
                'label' => 'Type',
            ])
            ->add('emailAddress', null, [
                'label' => 'Organisateur',
            ])
            ->add('postalCode', null, [
                'label' => 'Code postal',
            ])
        ;
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
            ->add('postalCode', null, [
                'label' => 'Code postal',
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
            ])
        ;
    }

    public function getExportFields()
    {
        return [
            'id',
            'type',
            'emailAddress',
            'postalCode',
            'convincedList',
            'almostConvincedList',
            'notConvinced',
            'reaction',
            'createdAt',
        ];
    }
}
