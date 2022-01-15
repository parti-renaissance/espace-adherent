<?php

namespace App\Admin;

use App\Form\ElectionRoundType;
use App\Form\PurifiedTextareaType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ElectionAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('introduction', PurifiedTextareaType::class, [
                'label' => 'Introduction',
                'purify_html_profile' => 'enrich_content',
                'attr' => ['class' => 'ck-editor'],
            ])
            ->add('proposalContent', PurifiedTextareaType::class, [
                'label' => 'Contenu affiché avant le bouton pour les propositions',
                'required' => false,
                'purify_html_profile' => 'enrich_content',
                'attr' => ['class' => 'ck-editor'],
            ])
            ->add('requestContent', PurifiedTextareaType::class, [
                'label' => 'Contenu affiché avant le bouton pour les demandes',
                'required' => false,
                'purify_html_profile' => 'enrich_content',
                'attr' => ['class' => 'ck-editor'],
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

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name', null, [
                'label' => 'Nom',
            ])
            ->add('introduction', null, [
                'label' => 'Introduction',
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
