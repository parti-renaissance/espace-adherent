<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\{
    Admin\AbstractAdmin, Datagrid\DatagridMapper, Datagrid\ListMapper, Form\FormMapper
};
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SkillAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
        : void
    {
        $formMapper
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'filter_emojis' => true,
            ])
            ->add('slug', null, [
                'label' => 'Slug',
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
        : void
    {
        $datagridMapper
            ->add('name', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ]);
    }

    protected function configureListFields(ListMapper $listMapper)
        : void
    {
        $listMapper
            ->addIdentifier('name', null, [
                'label' => 'Nom',
            ])
            ->add('slug', null, [
                'label' => 'Slug',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }
}
