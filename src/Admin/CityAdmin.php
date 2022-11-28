<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CityAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->with('Metadonnées', ['class' => 'col-md-6'])
                ->add('name', TextType::class, [
                    'label' => 'Nom',
                ])
                ->add('inseeCode', TextType::class, [
                    'label' => 'Code INSEE',
                ])
                ->add('postalCodes', CollectionType::class, [
                    'entry_type' => TextType::class,
                    'label' => 'Codes postaux',
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                ])
                ->add('department', null, [
                    'label' => 'Département',
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('name', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('inseeCode', null, [
                'label' => 'Code INSEE',
                'show_filter' => true,
            ])
            ->add('postalCodes', null, [
                'label' => 'Code postal',
                'show_filter' => true,
            ])
            ->add('department', null, [
                'label' => 'Département',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('name', null, [
                'label' => 'Nom',
            ])
            ->add('inseeCode', null, [
                'label' => 'Code INSEE',
            ])
            ->add('postalCodes', 'array', [
                'label' => 'Codes postaux',
            ])
            ->add('department', null, [
                'label' => 'Département',
            ])
            ->add('department.region', null, [
                'label' => 'Région',
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

    protected function configureExportFields(): array
    {
        return [
            'ID' => 'id',
            'Nom' => 'name',
            'Code INSEE' => 'inseeCode',
            'Code postal' => 'postalCodes',
            'Département' => 'department',
            'Région' => 'department.region',
        ];
    }
}
