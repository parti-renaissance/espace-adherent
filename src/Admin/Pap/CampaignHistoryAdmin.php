<?php

declare(strict_types=1);

namespace App\Admin\Pap;

use App\Admin\AbstractAdmin;
use App\Entity\Adherent;
use App\Pap\CampaignHistoryStatusEnum;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CampaignHistoryAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept('list');
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
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
            ->add('questioner', ModelFilter::class, [
                'label' => 'Militant',
                'show_filter' => true,
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'class' => Adherent::class,
                    'property' => [
                        'firstName',
                        'lastName',
                    ],
                ],
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('lastName', null, [
                'label' => 'Nom',
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

    protected function configureListFields(ListMapper $list): void
    {
        $list
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
            ->add('firstName', null, [
                'label' => 'Contacté - prénom',
            ])
            ->add('lastName', null, [
                'label' => 'Contacté - nom',
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
