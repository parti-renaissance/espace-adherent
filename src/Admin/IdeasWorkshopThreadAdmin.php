<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Form\Type\DateRangePickerType;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;

class IdeasWorkshopThreadAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('Fil de discussion')
                ->add('content', null, [
                    'label' => 'Nom',
                    'format_title_case' => true,
                ])
                ->add('answer.idea', null, [
                    'label' => 'Proposition concernée',
                ])
                ->add('author', null, [
                    'label' => 'Créé par',
                ])
                ->add('approved', null, [
                    'label' => 'Approuvé',
                ])
                ->add('createdAt', null, [
                    'label' => 'Créé le',
                    'format' => 'd M Y H:i',
                ])
            ->end()
            ->with('Commentaires')
                ->add('threadComments', null, [
                    'label' => 'commentaires',
                    'template' => 'admin/ideas_workshop/thread/show_thread_comments.html.twig',
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('content', null, [
                'label' => 'Contenu',
                'show_filter' => true,
            ])
            ->add('answer.idea', null, [
                'label' => 'Proposition concernée',
            ])
            ->add('author.lastName', null, [
                'label' => 'Nom du créateur',
                'show_filter' => true,
            ])
            ->add('author.firstName', null, [
                'label' => 'Prénom du créateur',
                'show_filter' => true,
            ])
            ->add('author.emailAddress', null, [
                'label' => 'Mail du créateur',
                'show_filter' => true,
            ])
            ->add('approved', null, [
                'label' => 'Approuvé',
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Créé le',
                'field_type' => DateRangePickerType::class,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('content', null, [
                'label' => 'Contenu du commentaire',
            ])
            ->add('answer.idea', null, [
                'label' => 'Proposition concernée',
            ])
            ->add('author', null, [
                'label' => 'Créé par',
            ])
            ->add('contributors', null, [
                'label' => 'Répondu/commenté par',
                'template' => 'admin/ideas_workshop/thread/list_thread_comments_contributors.html.twig',
            ])
            ->add('createdAt', null, [
                'label' => 'Créé le',
            ])
            ->add('lastCommentDate', null, [
                'label' => 'Répondu/commenté le',
                'template' => 'admin/ideas_workshop/thread/list_thread_last_comment.html.twig',
            ])
            ->add('approved', null, [
                'label' => 'Approuvé',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'show' => [],
                    'moderate' => [
                        'template' => 'admin/ideas_workshop/list_action_moderate.html.twig',
                    ],
                ],
            ])
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(['show', 'list']);
    }
}
