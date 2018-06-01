<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Mooc\Chapter;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MoocAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->tab('MOOC')
                ->with('Général', ['class' => 'col-md-6'])
                    ->add('title', TextType::class, [
                        'label' => 'Titre',
                    ])
                    ->add('description', TextType::class, [
                        'label' => 'Description',
                    ])
                    ->add('slug', TextType::class, [
                        'label' => 'Slug',
                        'disabled' => true,
                    ])
                ->end()
            ->end()
        ;

        if (!$this->request->isXmlHttpRequest()) {
            $formMapper
                ->with('Chapitres', ['class' => 'col-md-6'])
                    ->add('chapters', EntityType::class, [
                        'class' => Chapter::class,
                        'by_reference' => false,
                        'label' => 'Chapitre',
                        'multiple' => true,
                    ])
                ->end()
            ;
        }

        $formMapper->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ])
            ->add('description', null, [
                'label' => 'Description',
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
            ->add('description', null, [
                'label' => 'Description',
            ])
            ->add('slug', null, [
                'label' => 'Slug',
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
