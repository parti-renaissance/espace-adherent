<?php

namespace AppBundle\Admin;

use AppBundle\Form\PurifiedTextareaType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ApproachAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_by' => 'position',
    ];

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('position', null, [
                'label' => 'Ordre d\'affichage',
            ])
            ->addIdentifier('title', null, [
                'label' => 'Titre',
            ])
            ->add('_action', null, [
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
                ->add('title', TextType::class, [
                    'label' => 'Titre',
                ])
                ->add('content', PurifiedTextareaType::class, [
                    'label' => 'Contenu',
                    'attr' => ['class' => 'ck-editor-advanced'],
                    'purifier_type' => 'enrich_content',
                    'filter_emojis' => true,
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
