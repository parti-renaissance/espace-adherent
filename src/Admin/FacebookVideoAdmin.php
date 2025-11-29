<?php

declare(strict_types=1);

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class FacebookVideoAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'position';
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
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

    protected function configureListFields(ListMapper $list): void
    {
        $list
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
