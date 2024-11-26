<?php

namespace App\Admin;

use App\Form\ElectionRoundType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ElectionAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('introduction', TextareaType::class, [
                'label' => 'Introduction',
            ])
            ->add('proposalContent', TextareaType::class, [
                'label' => 'Contenu affiché avant le bouton pour les propositions',
                'required' => false,
            ])
            ->add('requestContent', TextareaType::class, [
                'label' => 'Contenu affiché avant le bouton pour les demandes',
                'required' => false,
            ])
            ->add('rounds', CollectionType::class, [
                'label' => 'Tours',
                'entry_type' => ElectionRoundType::class,
                'prototype' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('name', null, [
                'label' => 'Nom',
            ])
            ->add('introduction', null, [
                'label' => 'Introduction',
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
}
