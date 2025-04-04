<?php

namespace App\Admin;

use App\Entity\Adherent;
use App\History\UserActionHistoryTypeEnum;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class UserActionHistoryAdmin extends AbstractAdmin
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
            ->add('adherent', ModelFilter::class, [
                'label' => 'Adhérent',
                'show_filter' => true,
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'property' => [
                        'search',
                    ],
                    'to_string_callback' => static function (Adherent $adherent): string {
                        return \sprintf(
                            '%s (%s) [%s]',
                            $adherent->getFullName(),
                            $adherent->getEmailAddress(),
                            $adherent->getId()
                        );
                    },
                ],
            ])
            ->add('impersonator', ModelFilter::class, [
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
                    'class' => UserActionHistoryTypeEnum::class,
                    'choice_label' => static function (UserActionHistoryTypeEnum $type): string {
                        return 'user_action_history.type.'.$type->value;
                    },
                    'multiple' => true,
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('adherent', null, [
                'label' => 'Adhérent',
                'template' => 'admin/user_action_history/list_adherent.html.twig',
            ])
            ->add('type', null, [
                'label' => 'Type',
                'template' => 'admin/user_action_history/list_type.html.twig',
            ])
            ->add('date', null, [
                'label' => 'Date',
            ])
            ->add('data', null, [
                'label' => 'Données',
                'template' => 'admin/user_action_history/list_data.html.twig',
            ])
            ->add('impersonator', null, [
                'label' => 'Administrateur',
            ])
        ;
    }
}
