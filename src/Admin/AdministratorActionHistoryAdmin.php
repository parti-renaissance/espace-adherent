<?php

declare(strict_types=1);

namespace App\Admin;

use App\History\AdministratorActionHistoryTypeEnum;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class AdministratorActionHistoryAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'date';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list']);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('administrator', ModelFilter::class, [
                'label' => 'Administrateur',
                'show_filter' => true,
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'property' => [
                        'emailAddress',
                    ],
                ],
            ])
            ->add('type', ChoiceFilter::class, [
                'label' => 'Type',
                'show_filter' => true,
                'field_type' => EnumType::class,
                'field_options' => [
                    'class' => AdministratorActionHistoryTypeEnum::class,
                    'choice_label' => static function (AdministratorActionHistoryTypeEnum $type): string {
                        return 'administrator_action_history.type.'.$type->value;
                    },
                    'multiple' => true,
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('administrator', null, [
                'label' => 'Administrateur',
            ])
            ->add('type', null, [
                'label' => 'Type',
                'template' => 'admin/administrator_action_history/list_type.html.twig',
            ])
            ->add('date', null, [
                'label' => 'Date',
            ])
            ->add('data', null, [
                'label' => 'Données',
                'template' => 'admin/administrator_action_history/list_data.html.twig',
            ])
        ;
    }
}
