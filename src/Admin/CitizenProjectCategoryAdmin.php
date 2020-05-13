<?php

namespace App\Admin;

use App\Entity\BaseEventCategory;
use App\Form\CitizenProjectCategorySkillType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CitizenProjectCategoryAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'ASC',
        '_sort_by' => 'name',
    ];

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('slug', null, [
                'label' => 'Slug',
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Visibilité',
                'choices' => [
                    'Visible' => BaseEventCategory::ENABLED,
                    'Masqué' => BaseEventCategory::DISABLED,
                ],
            ])
            ->add('categorySkills', CollectionType::class, [
                'label' => 'Liste des compétences',
                'entry_type' => CitizenProjectCategorySkillType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'error_bubbling' => false,
                'attr' => [
                    'class' => 'category-skills-bloc',
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
            ->add('slug', null, [
                'label' => 'Slug',
            ])
            ->add('status', null, [
                'label' => 'Visibilité',
                'template' => 'admin/citizen_project_category/list_status.html.twig',
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
