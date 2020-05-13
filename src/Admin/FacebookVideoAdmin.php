<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class FacebookVideoAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by' => 'position',
    ];

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('facebookUrl', UrlType::class, [
                'label' => 'URL Facebook',
            ])
            ->add('twitterUrl', UrlType::class, [
                'label' => 'URL Twitter',
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
            ])
            ->add('author', null, [
                'label' => 'Auteur',
            ])
            ->add('position', null, [
                'label' => 'Position',
            ])
            ->add('published', null, [
                'label' => 'Publié ?',
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('facebookUrl', null, [
                'label' => 'URL Facebook',
            ])
            ->add('twitterUrl', null, [
                'label' => 'URL Twitter',
            ])
            ->add('description', null, [
                'label' => 'Description',
            ])
            ->add('author', null, [
                'label' => 'Auteur',
            ])
            ->add('position', null, [
                'label' => 'Position',
            ])
            ->add('published', null, [
                'label' => 'Publié ?',
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
