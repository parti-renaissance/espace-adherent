<?php

namespace App\Admin;

use App\Scope\AppEnum;
use App\Scope\FeatureEnum;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ScopeAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list', 'edit']);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Metadonnées 🧱', ['class' => 'col-md-6'])
                ->add('code', TextType::class, [
                    'label' => 'Code',
                    'disabled' => true,
                ])
                ->add('name', TextType::class, [
                    'label' => 'Nom',
                ])
                ->add('apps', ChoiceType::class, [
                    'label' => 'Applications',
                    'choices' => AppEnum::ALL,
                    'choice_label' => function (string $choice) {
                        return "scope.app.$choice";
                    },
                    'multiple' => true,
                    'expanded' => true,
                    'required' => false,
                ])
            ->end()
            ->with('Fonctionnalités 🗝️️', ['class' => 'col-md-6'])
                ->add('features', ChoiceType::class, [
                    'label' => 'Code',
                    'choices' => FeatureEnum::ALL,
                    'choice_label' => function (string $choice) {
                        return "scope.feature.$choice";
                    },
                    'multiple' => true,
                    'expanded' => true,
                    'required' => false,
                ])
                ->add('canaryFeatures', ChoiceType::class, [
                    'label' => 'Fonctionnalités en test',
                    'choices' => FeatureEnum::ALL,
                    'choice_label' => function (string $choice) {
                        return "scope.feature.$choice";
                    },
                    'multiple' => true,
                    'required' => false,
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('name', null, [
                'label' => 'Nom',
            ])
            ->add('apps', null, [
                'label' => 'Applications',
                'template' => 'admin/scope/list_apps.html.twig',
            ])
            ->add('features', 'array', [
                'label' => 'Fonctionnalités',
                'template' => 'admin/scope/list_features.html.twig',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }
}
