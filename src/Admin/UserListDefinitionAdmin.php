<?php

namespace App\Admin;

use App\Entity\UserListDefinitionEnum;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UserListDefinitionAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'id';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add('label');
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->remove('delete')
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
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
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $isCreation = null === $this->getSubject()->getId();

        $form
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
