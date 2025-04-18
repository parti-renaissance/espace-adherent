<?php

namespace App\Admin;

use App\AppSession\SessionStatusEnum;
use App\AppSession\SystemEnum;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AppSessionAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_BY] = 'lastActivityDate';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list', 'show']);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('adherent', ModelFilter::class, [
                'label' => 'Militant',
                'show_filter' => true,
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'property' => [
                        'search',
                    ],
                ],
            ])
            ->add('status', ChoiceFilter::class, [
                'label' => 'Statut',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => SessionStatusEnum::all(),
                    'choice_label' => fn (SessionStatusEnum $status) => $status->value,
                ],
            ])
            ->add('client', ModelFilter::class, [
                'label' => 'App',
                'show_filter' => true,
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'minimum_input_length' => 0,
                    'property' => ['name'],
                ],
            ])
            ->add('appSystem', ChoiceFilter::class, [
                'label' => 'Système',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'multiple' => true,
                    'choices' => SystemEnum::all(),
                    'choice_label' => fn (SystemEnum $system) => $system->value,
                ],
            ])
            ->add('appVersion', null, [
                'label' => 'Version',
                'show_filter' => true,
            ])
            ->add('lastActivityDate', DateRangeFilter::class, [
                'label' => 'Dernière activité',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Créée le',
                'field_type' => DateRangePickerType::class,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('adherent.id', null, [
                'label' => 'ID',
                'template' => 'admin/adherent/list_identifier.html.twig',
            ])
            ->add('adherent', null, [
                'label' => 'Prénom Nom',
                'template' => 'admin/adherent/list_fullname_certified.html.twig',
            ])
            ->add('status', 'enum', [
                'label' => 'Statut',
                'use_value' => true,
                'enum_translation_domain' => 'messages',
            ])
            ->add('client', null, ['label' => 'App'])
            ->add('appSystem', 'enum', [
                'label' => 'Système',
                'use_value' => true,
            ])
            ->add('appVersion', null, ['label' => 'Version'])
            ->add('createdAt', null, ['label' => 'Date de création'])
            ->add('lastActivityDate', null, ['label' => 'Dernière activité'])
            ->add(ListMapper::NAME_ACTIONS, null, ['actions' => ['show' => []]])
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('adherent.id', null, [
                'label' => 'ID',
                'template' => 'admin/adherent/show_identifier.html.twig',
            ])
            ->add('adherent', null, [
                'label' => 'Prénom Nom',
                'template' => 'admin/adherent/show_fullname_certified.html.twig',
            ])
            ->add('status', 'enum', [
                'label' => 'Statut',
                'use_value' => true,
                'enum_translation_domain' => 'messages',
            ])
            ->add('client', null, ['label' => 'App'])
            ->add('appSystem', 'enum', [
                'label' => 'Système',
                'use_value' => true,
            ])
            ->add('appVersion', null, ['label' => 'Version'])
            ->add('createdAt', null, ['label' => 'Créée le'])
            ->add('updatedAt', null, ['label' => 'Modifiée le'])
            ->add('lastActivityDate', null, ['label' => 'Dernière activité'])
            ->add('uuid', null, ['label' => 'UUID'])
            ->add('userAgent', null, ['label' => 'User-Agent'])
        ;
    }
}
