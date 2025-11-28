<?php

declare(strict_types=1);

namespace App\Admin\Procuration;

use App\Form\Admin\Procuration\RoundType;
use App\Form\Admin\SimpleMDEContent;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ElectionAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Général', ['class' => 'col-md-6'])
                ->add('name', TextType::class, [
                    'label' => 'Nom',
                ])
                ->add('slug', TextType::class, [
                    'label' => 'Slug',
                ])
            ->end()
            ->with('Tours', ['class' => 'col-md-6'])
                ->add('rounds', CollectionType::class, [
                    'label' => false,
                    'entry_type' => RoundType::class,
                    'prototype' => true,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                ])
            ->end()
            ->with('Demande', ['class' => 'col-md-6'])
                ->add('requestTitle', SimpleMDEContent::class, [
                    'label' => 'Titre',
                    'attr' => ['rows' => 20],
                    'help' => 'help.markdown',
                    'help_html' => true,
                ])
                ->add('requestDescription', SimpleMDEContent::class, [
                    'label' => 'Description',
                    'attr' => ['rows' => 20],
                    'help' => 'help.markdown',
                    'help_html' => true,
                ])
                ->add('requestConfirmation', SimpleMDEContent::class, [
                    'label' => 'Confirmation',
                    'attr' => ['rows' => 20],
                    'help' => 'help.markdown',
                    'help_html' => true,
                ])
                ->add('requestLegal', SimpleMDEContent::class, [
                    'label' => 'Légal',
                    'attr' => ['rows' => 20],
                    'help' => 'help.markdown',
                    'help_html' => true,
                ])
            ->end()
            ->with('Proposition', ['class' => 'col-md-6'])
                ->add('proxyTitle', SimpleMDEContent::class, [
                    'label' => 'Titre',
                    'attr' => ['rows' => 20],
                    'help' => 'help.markdown',
                    'help_html' => true,
                ])
                ->add('proxyDescription', SimpleMDEContent::class, [
                    'label' => 'Description',
                    'attr' => ['rows' => 20],
                    'help' => 'help.markdown',
                    'help_html' => true,
                ])
                ->add('proxyConfirmation', SimpleMDEContent::class, [
                    'label' => 'Confirmation',
                    'attr' => ['rows' => 20],
                    'help' => 'help.markdown',
                    'help_html' => true,
                ])
                ->add('proxyLegal', SimpleMDEContent::class, [
                    'label' => 'Légal',
                    'attr' => ['rows' => 20],
                    'help' => 'help.markdown',
                    'help_html' => true,
                ])
            ->end()
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('name', null, [
                'label' => 'Nom',
            ])
            ->add('slug', null, [
                'label' => 'Slug',
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

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('slug', null, [
                'label' => 'Slug',
                'show_filter' => true,
            ])
        ;
    }
}
