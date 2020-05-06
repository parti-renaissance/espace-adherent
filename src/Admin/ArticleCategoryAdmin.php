<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class ArticleCategoryAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'filter_emojis' => true,
            ])
            ->add('position', null, [
                'label' => 'Position',
            ])
            ->add('slug', null, [
                'label' => 'Slug',
            ])
            ->add('ctaLink', UrlType::class, [
                'required' => false,
                'label' => 'Lien d\'action',
            ])
            ->add('ctaLabel', null, [
                'label' => 'Label d\'action',
            ])
            ->add('display', null, [
                'label' => 'Affichée ?',
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name', null, [
                'label' => 'Nom',
            ])
            ->add('position', null, [
                'label' => 'Position',
            ])
            ->add('slug', null, [
                'label' => 'Slug',
            ])
            ->add('ctaLink', null, [
                'label' => 'CTA Link',
            ])
            ->add('ctaLabel', null, [
                'label' => 'CTA Label',
            ])
            ->add('display', null, [
                'label' => 'Affichée ?',
                'editable' => true,
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
