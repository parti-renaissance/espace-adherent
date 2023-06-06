<?php

namespace App\Admin;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

class TimelineProfileAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Traductions', ['class' => 'col-md-6'])
                ->add('translations', TranslationsType::class, [
                    'by_reference' => false,
                    'label' => false,
                    'fields' => [
                        'title' => [
                            'label' => 'Titre',
                        ],
                        'slug' => [
                            'label' => 'URL de publication',
                            'sonata_help' => 'Ne spécifier que la fin : http://en-marche.fr/timeline/profil/[votre-valeur]<br />Doit être unique',
                        ],
                        'description' => [
                            'label' => 'Description',
                        ],
                    ],
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('translations.title', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ])

            ->add('translations.description', null, [
                'label' => 'Description',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('title', null, [
                'label' => 'Titre',
                'virtual_field' => true,
                'template' => 'admin/timeline/profile/list_title.html.twig',
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
            'Titre (FR, EN)' => 'exportTitles',
            'Slug (FR, EN)' => 'exportSlugs',
            'Description (FR, EN)' => 'exportDescriptions',
        ];
    }
}
