<?php

namespace AppBundle\Admin;

use AppBundle\Form\Admin\Oldolf\MarkerType;
use AppBundle\Form\Admin\Oldolf\MeasureType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class OldolfCityAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Metadonnées', ['class' => 'col-md-6'])
                ->add('name', TextType::class, [
                    'label' => 'Nom',
                ])
                ->add('postalCodes', CollectionType::class, [
                    'entry_type' => TextType::class,
                    'label' => 'Codes postaux',
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                ])
                ->add('inseeCode', TextType::class, [
                    'label' => 'Code INSEE',
                ])
                ->add('department', null, [
                    'label' => 'Département',
                ])
                ->add('slug', TextType::class, [
                    'label' => 'Slug',
                    'help' => '(code_insee-nom) Exemple: 06088-nice, 59350-lille',
                ])
            ->end()
            ->with('Géolocalisation', ['class' => 'col-md-6'])
                ->add('latitude', NumberType::class, [
                    'label' => 'Latitude',
                ])
                ->add('longitude', NumberType::class, [
                    'label' => 'Longitude',
                ])
            ->end()
            ->with('Textes', ['class' => 'col-md-8'])
                ->add('measures', CollectionType::class, [
                    'entry_type' => MeasureType::class,
                    'label' => false,
                    'required' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                ])
            ->end()
            ->with('Marqueurs', ['class' => 'col-md-4'])
                ->add('markers', CollectionType::class, [
                    'entry_type' => MarkerType::class,
                    'label' => false,
                    'required' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
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
            ->add('postalCodes', null, [
                'label' => 'Codes postal',
                'show_filter' => true,
            ])
            ->add('inseeCode', null, [
                'label' => 'Code INSEE',
                'show_filter' => true,
            ])
            ->add('department', null, [
                'label' => 'Départment',
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
            ->add('postalCodes', null, [
                'label' => 'Codes postaux',
            ])
            ->add('inseeCode', null, [
                'label' => 'Code INSEE',
            ])
            ->add('department', null, [
                'label' => 'Départment',
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
            'Codes postaux' => 'exportPostalCodes',
            'Code INSEE' => 'inseeCode',
            'Département' => 'department',
            'Slug' => 'slug',
            'Latitude' => 'latitude',
            'Longitude' => 'longitude',
        ];
    }
}
