<?php

namespace App\Admin\Reporting;

use App\Entity\Reporting\TeamMemberHistory;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TeamMemberHistoryAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 128,
        '_sort_order' => 'DESC',
        '_sort_by' => 'date',
    ];

    public function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept('list');
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
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

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
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
