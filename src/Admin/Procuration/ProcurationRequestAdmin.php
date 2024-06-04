<?php

namespace App\Admin\Procuration;

use App\Admin\AbstractAdmin;
use App\Form\Admin\Procuration\InitialRequestTypeEnumType;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProcurationRequestAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('email', TextType::class, ['label' => 'Adresse email']);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('email', null, [
                'label' => 'Adresse email',
            ])
            ->add('type', null, [
                'label' => 'Type',
                'template' => 'admin/procuration_v2/_list_initial_request_type.html.twig',
            ])
            ->add('createdAt', null, [
                'label' => 'Date',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('email', null, [
                'label' => 'Adresse email',
                'show_filter' => true,
            ])
            ->add('type', ChoiceFilter::class, [
                'label' => 'Type',
                'show_filter' => true,
                'field_type' => InitialRequestTypeEnumType::class,
                'field_options' => [
                    'multiple' => true,
                ],
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Date de création',
                'field_type' => DateRangePickerType::class,
                'show_filter' => true,
            ])
        ;
    }
}
