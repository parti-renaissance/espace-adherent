<?php

namespace AppBundle\Admin;

use AppBundle\Form\InteractiveChoiceType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;


class InteractiveAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->add('slug')
            ->add('meta')
            ->add('choices', CollectionType::class, [
                'entry_type' => InteractiveChoiceType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list)
    {
        $list
            ->add('id', null, [
                'label' => 'Clé',
            ])
            ->add('slug', null, [
                'label' => 'Slug',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'template' => 'admin/interactive/choice_list_actions.html.twig',
            ])
        ;
    }
}
