<?php

declare(strict_types=1);

namespace App\Admin\Phoning;

use App\Phoning\CampaignHistoryEngagementEnum;
use App\Phoning\CampaignHistoryStatusEnum;
use App\Phoning\CampaignHistoryTypeEnum;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
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
            ->add('adherent', ModelFilter::class, [
                'label' => 'Appelé',
                'show_filter' => true,
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'property' => [
                        'firstName',
                        'lastName',
                    ],
                ],
            ])
            ->add('caller', ModelFilter::class, [
                'label' => 'Appelant',
                'show_filter' => true,
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'property' => [
                        'firstName',
                        'lastName',
                    ],
                ],
            ])
            ->add('beginAt', DateRangeFilter::class, [
                'label' => 'Date de début',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
            ])
            ->add('status', null, [
                'label' => 'Statut',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => array_flip(CampaignHistoryStatusEnum::LABELS),
                ],
            ])
            ->add('type', ChoiceFilter::class, [
                'label' => 'Mode',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => array_flip(CampaignHistoryTypeEnum::LABELS),
                ],
            ])
            ->add('postalCodeChecked', null, [
                'label' => 'Code postal à jour ?',
            ])
            ->add('needEmailRenewal', null, [
                'label' => 'Réabonnement aux emails ?',
            ])
            ->add('needSmsRenewal', null, [
                'label' => 'Réabonnement aux SMS ?',
            ])
            ->add('engagement', ChoiceFilter::class, [
                'label' => '(Ré)engagement',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => array_flip(CampaignHistoryEngagementEnum::LABELS),
                ],
            ])
            ->add('note', null, [
                'label' => 'Note',
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('campaign', null, [
                'label' => 'Campagne',
            ])
            ->add('dataSurvey.survey', null, [
                'label' => 'Questionnaire',
                'template' => 'admin/phoning/campaign_history/list_survey.html.twig',
            ])
            ->add('adherent', null, [
                'label' => 'Appelé',
            ])
            ->add('call_info', null, [
                'label' => 'Date de l\'appel',
                'virtual_field' => true,
                'template' => 'admin/phoning/campaign_history/list_call_info.html.twig',
            ])
            ->add('caller', null, [
                'label' => 'Appelant',
            ])
            ->add('status', null, [
                'label' => 'Statut',
                'template' => 'admin/phoning/campaign_history/list_status.html.twig',
            ])
            ->add('type', null, [
                'label' => 'Mode',
                'template' => 'admin/phoning/campaign_history/list_type.html.twig',
            ])
            ->add('satisfaction_questions', null, [
                'label' => 'Réponses de satisfaction',
                'virtual_field' => true,
                'template' => 'admin/phoning/campaign_history/list_satisfaction_questions.html.twig',
            ])
        ;
    }
}
