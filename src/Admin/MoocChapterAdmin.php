<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Mooc\Video;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\CoreBundle\Form\Type\BooleanType;
use Sonata\CoreBundle\Form\Type\DatePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MoocChapterAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->tab('Chapitres')
                ->with('Général', ['class' => 'col-md-6'])
                    ->add('title', TextType::class, [
                        'label' => 'Titre',
                        'filter_emojis' => true,
                    ])
                    ->add('slug', TextType::class, [
                        'label' => 'Slug',
                        'disabled' => true,
                    ])
                    ->add('published', BooleanType::class, [
                        'label' => 'Publié',
                    ])
                    ->add('publishedAt', DatePickerType::class, [
                        'label' => 'Date de publication',
                    ])
                    ->add('displayOrder', IntegerType::class, [
                        'label' => 'Ordre d\'affichage',
                        'scale' => 0,
                        'attr' => [
                            'min' => 0,
                        ],
                    ])
                ->end()
                ->with('Media', ['class' => 'col-md-6'])
                    ->add('videos', EntityType::class, [
                        'label' => 'Vidéos',
                        'multiple' => true,
                        'class' => Video::class,
                        'by_reference' => false,
                    ])
                ->end()
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ])
            ->add('displayOrder', null, [
                'label' => 'Ordre d\'affichage',
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
            ->add('slug', null, [
                'label' => 'Slug',
            ])
            ->add('published', null, [
                'label' => 'Publié',
            ])
            ->add('publishedAt', null, [
                'label' => 'Date de publication',
            ])
            ->add('mooc', null, [
                'label' => 'MOOC associé',
            ])
            ->add('displayOrder', null, [
                'label' => 'Ordre d\'affichage',
            ])
            ->add('_action', null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('show');
    }
}
