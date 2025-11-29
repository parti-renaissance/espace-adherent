<?php

declare(strict_types=1);

namespace App\Admin\Reporting;

use App\Entity\Reporting\TeamMemberHistory;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TeamMemberHistoryAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'date';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
        $sortValues[DatagridInterface::PER_PAGE] = 128;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept('list');
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('team.name', null, [
                'label' => 'Nom de l\'équipe',
                'show_filter' => true,
            ])
            ->add('adherent.emailAddress', null, [
                'label' => 'Email adhérent',
                'show_filter' => true,
            ])
            ->add('administrator', null, [
                'label' => 'Administrateur',
                'show_filter' => true,
            ])
            ->add('action', ChoiceFilter::class, [
                'label' => 'Action',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => TeamMemberHistory::ACTION_CHOICES,
                    'choice_label' => function (string $choice) {
                        return "team_member_history.action.$choice";
                    },
                ],
            ])
            ->add('date', DateRangeFilter::class, [
                'label' => 'Date',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('team', null, [
                'label' => 'Équipe',
            ])
            ->add('adherent', null, [
                'label' => 'Adhérent',
                'template' => 'admin/reporting/team_member_history/list_adherent.html.twig',
            ])
            ->add('administrator', null, [
                'label' => 'Administrateur',
            ])
            ->add('action', null, [
                'label' => 'Action',
                'template' => 'admin/reporting/team_member_history/list_action.html.twig',
            ])
            ->add('date', null, [
                'label' => 'Date',
            ])
        ;
    }
}
