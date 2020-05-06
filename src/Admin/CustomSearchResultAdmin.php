<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CustomSearchResultAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 64,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('title', null, [
                'label' => 'Titre (priorité la plus élevée dans la recherche)',
            ])
            ->add('media', null, [
                'label' => 'Image du résultat de recherche',
            ])
            ->add('keywords', null, [
                'label' => 'Mots-clés (priorité scondaire dans la recherche)',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description (priorité la plus faible dans la recherche)',
            ])
            ->add('url', null, [
                'label' => 'URL du résultat de recherche',
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ])
            ->add('keywords', null, [
                'label' => 'Mot-clé',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, [
                'label' => 'Titre',
            ])
            ->add('keywords', null, [
                'label' => 'Mots-clés',
            ])
            ->add('url', null, [
                'label' => 'URL',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
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
}
