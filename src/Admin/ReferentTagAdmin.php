<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ReferentTagAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'name';
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add('name');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('code', null, [
                'label' => 'Code',
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('code', TextType::class, [
                'label' => 'Code',
                'help' => 'Ne doit contenir que des lettres, chiffres et tirets (-).',
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('code', null, [
                'label' => 'Code',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }
}
