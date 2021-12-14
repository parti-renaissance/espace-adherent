<?php

namespace App\Admin\Pap;

use App\Admin\AbstractAdmin;
use App\Pap\CampaignHistoryStatusEnum;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CampaignHistoryAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept('list');
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('campaign', null, [
                'label' => 'Campagne',
                'show_filter' => true,
            ])
            ->add('dataSurvey.survey', null, [
                'label' => 'Questionnaire',
                'show_filter' => true,
            ])
            ->add('questioner', ModelAutocompleteFilter::class, [
                'label' => 'Militant',
                'show_filter' => true,
                'field_options' => [
                    'property' => [
                        'firstName',
                        'lastName',
                    ],
                ],
            ])
            ->add('adherent', ModelAutocompleteFilter::class, [
                'label' => 'Contacté',
                'field_options' => [
                    'property' => [
                        'firstName',
                        'lastName',
                    ],
                ],
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Date',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('status', null, [
                'label' => 'Statut',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => array_flip(CampaignHistoryStatusEnum::LABELS),
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('campaign', null, [
                'label' => 'Campagne',
            ])
            ->add('building.address', null, [
                'label' => 'Adresse',
                'template' => 'admin/pap/campaign_history/list_address.html.twig',
            ])
            ->add('buildingBlock', null, [
                'label' => 'Bâtiment',
            ])
            ->add('floor', null, [
                'label' => 'Étage',
                'template' => 'admin/pap/campaign_history/list_floor.html.twig',
            ])
            ->add('door', null, [
                'label' => 'Porte',
            ])
            ->add('adherent', null, [
                'label' => 'Contacté',
            ])
            ->add('questioner', null, [
                'label' => 'Militant',
            ])
            ->add('dataSurvey.survey', null, [
                'label' => 'Questionnaire',
                'template' => 'admin/pap/campaign_history/list_survey.html.twig',
            ])
            ->add('status', null, [
                'label' => 'Statut',
                'template' => 'admin/pap/campaign_history/list_status.html.twig',
            ])
            ->add('createdAt', null, [
                'label' => 'Date',
            ])
        ;
    }
}
