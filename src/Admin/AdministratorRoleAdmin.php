<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\AdministratorRole;
use App\Form\Admin\AdministratorRoleGroupEnumType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AdministratorRoleAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list', 'edit']);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        /** @var AdministratorRole $administratorRole */
        $administratorRole = $this->getSubject();

        $form
            ->with('Metadonnées 🧱', ['class' => 'col-md-6'])
                ->add('code', TextType::class, [
                    'label' => 'Code',
                    'disabled' => true,
                ])
                ->add('label', TextType::class, [
                    'label' => 'Label',
                ])
                ->add('enabled', CheckboxType::class, [
                    'label' => 'Activé',
                    'required' => false,
                    'disabled' => 'ROLE_SUPER_ADMIN' === $administratorRole->code,
                ])
                ->add('groupCode', AdministratorRoleGroupEnumType::class, [
                    'label' => 'Groupe',
                ])
                ->add('description', TextareaType::class, [
                    'label' => 'Description',
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('code', null, [
                'label' => 'Code',
                'show_filter' => true,
            ])
            ->add('label', null, [
                'label' => 'Label',
                'show_filter' => true,
            ])
            ->add('enabled', null, [
                'label' => 'Activé ?',
                'show_filter' => true,
            ])
            ->add('groupCode', ChoiceFilter::class, [
                'label' => 'Groupe',
                'show_filter' => true,
                'field_type' => AdministratorRoleGroupEnumType::class,
                'field_options' => [
                    'multiple' => true,
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('code', null, [
                'label' => 'Code',
            ])
            ->add('label', null, [
                'label' => 'Label',
            ])
            ->add('enabled', null, [
                'label' => 'Activé',
            ])
            ->add('groupCode', null, [
                'label' => 'Groupe',
                'template' => 'admin/admin/role/list_groupCode.html.twig',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }
}
