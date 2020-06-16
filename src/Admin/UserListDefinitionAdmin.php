<?php

namespace App\Admin;

use App\Entity\UserListDefinitionEnum;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UserListDefinitionAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'id',
    ];

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter->add('label');
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('delete')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('label', null, [
                'label' => 'Nom',
            ])
            ->add('code', null, [
                'label' => 'Code',
            ])
            ->add('type', null, [
                'label' => 'Type',
                'template' => 'admin/user_list_definition/list_type.html.twig',
            ])
            ->add('color', 'color', [
                'label' => 'Couleur',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $isCreation = null === $this->getSubject()->getId();

        $formMapper
            ->add('label', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('code', TextType::class, [
                'label' => 'Code',
                'disabled' => !$isCreation,
                'attr' => [
                    'placeholder' => 'supporting-la-rem',
                ],
                'help' => 'Laissez le champ vide pour que le système génère cette valeur automatiquement',
                'required' => false,
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'placeholder' => '--',
                'choices' => UserListDefinitionEnum::CHOICES,
                'disabled' => !$isCreation,
            ])
            ->add('color', ColorType::class, [
                'attr' => [
                    'class' => 'input-lg',
                ],
            ])
        ;
    }
}
