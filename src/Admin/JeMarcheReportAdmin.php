<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class JeMarcheReportAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureShowFields(ShowMapper $show): void
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
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
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

    protected function configureListFields(ListMapper $list): void
    {
        $list
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
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'show' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureExportFields(): array
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
