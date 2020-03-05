<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CityAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
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
                ->add('country', CountryType::class, [
                    'label' => 'Pays',
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
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
            ->add('country', ChoiceFilter::class, [
                'label' => 'Pays',
                'field_type' => CountryType::class,
                'field_options' => [
                    'multiple' => true,
                ],
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
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
            ->add('country', null, [
                'label' => 'Pays',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    public function getExportFields()
    {
        return [
            'ID' => 'id',
            'Nom' => 'name',
            'Code INSEE' => 'inseeCode',
            'Code postal' => 'postalCodes',
            'Pays' => 'country',
        ];
    }
}
