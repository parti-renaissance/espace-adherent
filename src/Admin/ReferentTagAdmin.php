<?php

namespace AppBundle\Admin;

use AppBundle\Entity\ReferentTag;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ReferentTagAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'ASC',
        '_sort_by' => 'name',
    ];

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter->add('name');
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('code', null, [
                'label' => 'Code',
            ])
            ->add('category', null, [
                'label' => 'Categorie',
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('code', TextType::class, [
                'label' => 'Code',
                'help' => 'Ne doit contenir que des lettres, chiffres et tirets (-).',
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Categorie',
                'choices' => [
                    'Département' => ReferentTag::CATEGORY_DEPARTMENT,
                    'Arrondissement' => ReferentTag::CATEGORY_ARRONDISSEMENT,
                    'Circonscritpion' => ReferentTag::CATEGORY_CIRCO,
                    'Europe' => ReferentTag::CATEGORY_EUROPE,
                    'Monde' => ReferentTag::CATEGORY_MONDE,
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('code', null, [
                'label' => 'Code',
            ])
            ->add('category', null, [
                'label' => 'Categorie',
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
