<?php

namespace AppBundle\Admin\Nomenclature;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SenatorAreaAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by' => 'rank',
    ];

    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
            ->add('code', null, [
                'label' => 'Code',
            ])
            ->add('name', null, [
                'label' => 'Nom de la zone',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $mapper)
    {
        $mapper
            ->with('Informations générales', ['class' => 'col-md-6'])
                ->add('code', null, [
                    'label' => 'Code',
                    'attr' => [
                        'placeholder' => '0001',
                        'maxlength' => '4',
                    ],
                ])
                ->add('name', null, [
                    'label' => 'Nom de la zone',
                ])
            ->end()
            ->with('Mots-clés', ['class' => 'col-md-6'])
                ->add('keywords', CollectionType::class, [
                    'required' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'delete_empty' => true,
                    'entry_type' => TextType::class,
                    'label' => 'Mot-clés',
                ])
            ->end()
        ;
    }
}
