<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class LiveLinkAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'id';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('position', null, [
                'label' => 'Position',
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('link', null, [
                'label' => 'Cible du lien',
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('title', null, [
                'label' => 'Titre',
            ])
            ->add('position', null, [
                'label' => 'Position',
            ])
            ->add('link', null, [
                'label' => 'Cible du lien',
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
