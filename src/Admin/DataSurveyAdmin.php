<?php

declare(strict_types=1);

namespace App\Admin;

use App\Repository\Jecoute\SurveyRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

class DataSurveyAdmin extends AbstractAdmin
{
    public function __construct(private readonly SurveyRepository $surveyRepository)
    {
        parent::__construct();
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'postedAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $query
            ->leftJoin('o.survey', 'survey')
            ->leftJoin('survey.questions', 'surveyQuestion')
            ->leftJoin('surveyQuestion.question', 'question')
            ->leftJoin('surveyQuestion.dataAnswers', 'dataAnswer')
            ->leftJoin('dataAnswer.selectedChoices', 'selectedChoice')
            ->leftJoin('o.jemarcheDataSurvey', 'jemarcheDataSurvey')
            ->leftJoin('o.phoningCampaignHistory', 'phoningCampaignHistory')
            ->leftJoin('o.papCampaignHistory', 'papCampaignHistory')
            ->addSelect('survey', 'surveyQuestion', 'question', 'dataAnswer', 'selectedChoice')
            ->addSelect('jemarcheDataSurvey', 'phoningCampaignHistory', 'papCampaignHistory')
        ;

        return $query;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept('list');
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('survey', null, [
                'label' => 'Questionnaire',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('type', null, [
                'label' => 'Type',
                'virtual_field' => true,
                'template' => 'admin/jecoute/data_survey/list_type.html.twig',
            ])
            ->add('author', null, [
                'label' => 'Auteur',
                'header_style' => 'min-width: 150px',
            ])
            ->add('interviewed', null, [
                'label' => 'Appelé/Intérrogé',
                'virtual_field' => true,
                'header_style' => 'min-width: 150px',
                'template' => 'admin/jecoute/data_survey/list_interviewed.html.twig',
            ])
            ->add('postedAt', null, [
                'label' => 'Publié',
                'header_style' => 'min-width: 150px',
            ])
        ;

        if (($filter = $this->getRequest()->query->all('filter'))
            && !empty($surveyId = $filter['survey']['value'])
        ) {
            $survey = $this->surveyRepository->find($surveyId);

            foreach ($survey->getQuestions() as $key => $surveyQuestion) {
                $list->add("response_$key", null, [
                    'header_style' => 'min-width: 250px',
                    'label' => $surveyQuestion->getQuestion()->getContent(),
                    'virtual_field' => true,
                    'template' => 'admin/jecoute/data_survey/list_response.html.twig',
                ]);
            }
        }
    }
}
