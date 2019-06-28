<?php

namespace AppBundle\Admin\Formation;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PathAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Module', ['class' => 'col-md-12'])
                ->add('title', TextType::class, [
                    'label' => 'Titre',
                ])
                ->add('slug', TextType::class, [
                    'label' => 'URL',
                    'disabled' => true,
                    'help' => 'Généré automatiquement depuis le titre.',
                ])
                ->add('description', TextareaType::class, [
                    'label' => 'description',
                ])
            ->end()
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, [
                'label' => 'Titre',
            ])
            ->add('description', null, [
                'label' => 'description',
            ])
            ->add('axes', null, [
                'label' => 'Axes de formation',
                'default' => '-',
            ])
            ->add('slug', null, [
                'label' => 'URL',
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
