<?php

namespace AppBundle\Admin\Reporting;

use AppBundle\Entity\Reporting\AdherentCertificationHistory;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AdherentCertificationHistoryAdmin extends AbstractAdmin
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
                    'choices' => AdherentCertificationHistory::ACTION_CHOICES,
                    'choice_label' => function (string $choice) {
                        return "adherent_certification_history.action.$choice";
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
            ->add('adherent', null, [
                'label' => 'Adhérent',
                'template' => 'admin/reporting/adherent_certification_history/list_adherent.html.twig',
            ])
            ->add('administrator', null, [
                'label' => 'Administrateur',
            ])
            ->add('action', null, [
                'label' => 'Action',
                'template' => 'admin/reporting/adherent_certification_history/list_action.html.twig',
            ])
            ->add('date', null, [
                'label' => 'Date',
            ])
        ;
    }
}
