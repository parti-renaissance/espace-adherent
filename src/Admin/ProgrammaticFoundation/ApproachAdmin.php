<?php

namespace App\Admin\ProgrammaticFoundation;

use App\Form\PurifiedTextareaType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ApproachAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_by' => 'position',
    ];

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, [
                'label' => 'Titre',
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
            ->add('position', null, [
                'label' => 'Ordre d\'affichage',
                'header_style' => 'width: 10%',
            ])
            ->add('_action', null, [
                'header_style' => 'width: 15%',
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Grand axe')
                ->add('title', null, [
                    'label' => 'Titre',
                ])
                ->add('content', PurifiedTextareaType::class, [
                    'label' => 'Contenu',
                    'attr' => ['class' => 'ck-editor-advanced'],
                    'purify_html_profile' => 'enrich_content',
                    'required' => false,
                ])
                ->add('position', IntegerType::class, [
                    'label' => 'Ordre d\'affichage',
                    'scale' => 0,
                    'attr' => [
                        'min' => 1,
                    ],
                ])
            ->end()
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('show');
    }
}
