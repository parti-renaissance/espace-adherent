<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DonatorTagAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'ASC',
        '_sort_by' => 'label',
    ];

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('label', TextType::class, [
                'label' => 'Label',
            ])
            ->add('color', ColorType::class, [
                'attr' => [
                    'class' => 'input-lg',
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('label', null, [
                'label' => 'Label',
            ])
            ->add('color', 'color', [
                'label' => 'Couleur',
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
