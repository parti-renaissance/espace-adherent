<?php

namespace App\Admin;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TimelineThemeAdmin extends AbstractAdmin
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
            ->with('Publication', ['class' => 'col-md-4'])
                ->add('featured', CheckboxType::class, [
                    'label' => 'Mise en avant',
                    'required' => false,
                ])
                ->add('media', null, [
                    'label' => 'Image principale',
                    'required' => false,
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('title', CallbackFilter::class, [
                'label' => 'Titre',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb
                        ->join("$alias.translations", 'translations')
                        ->andWhere('translations.title LIKE :title')
                        ->setParameter('title', '%'.$value->getValue().'%')
                    ;

                    return true;
                },
            ])
            ->add('slug', CallbackFilter::class, [
                'label' => 'URL',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb
                        ->join("$alias.translations", 'translations')
                        ->andWhere('translations.slug LIKE :slug')
                        ->setParameter('slug', '%'.$value->getValue().'%')
                    ;

                    return true;
                },
            ])
            ->add('description', CallbackFilter::class, [
                'label' => 'Description',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb
                        ->join("$alias.translations", 'translations')
                        ->andWhere('translations.description LIKE :description')
                        ->setParameter('description', '%'.$value->getValue().'%')
                    ;

                    return true;
                },
            ])
            ->add('featured', null, [
                'label' => 'Mise en avant',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('_thumbnail', null, [
                'label' => 'Image',
                'virtual_field' => true,
                'template' => 'admin/timeline/theme/list_image.html.twig',
            ])
            ->addIdentifier('title', null, [
                'label' => 'Titre',
                'virtual_field' => true,
                'template' => 'admin/timeline/theme/list_title.html.twig',
            ])
            ->add('featured', null, [
                'label' => 'Mise en avant',
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
            'Mise en avant' => 'featured',
        ];
    }
}
