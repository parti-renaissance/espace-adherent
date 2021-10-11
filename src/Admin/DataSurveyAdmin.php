<?php

namespace App\Admin;

use App\Repository\Jecoute\SurveyRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class DataSurveyAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'postedAt',
    ];

    /** @var SurveyRepository */
    private $surveyRepository;

    public function createQuery($context = 'list')
    {
        $queryBuilder = parent::createQuery($context);

        if ('list' === $context) {
            $queryBuilder
                ->leftJoin('o.survey', 'survey')
                ->leftJoin('survey.questions', 'surveyQuestion')
                ->leftJoin('surveyQuestion.question', 'question')
                ->leftJoin('surveyQuestion.dataAnswers', 'dataAnswer')
                ->leftJoin('dataAnswer.selectedChoices', 'selectedChoice')
                ->leftJoin('o.jemarcheDataSurvey', 'jemarcheDataSurvey')
                ->leftJoin('o.campaignHistory', 'campaignHistory')
                ->addSelect('survey', 'surveyQuestion', 'question', 'dataAnswer', 'selectedChoice', 'jemarcheDataSurvey', 'campaignHistory')
            ;
        }

        return $queryBuilder;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept('list');
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('survey', null, [
                'label' => 'Questionnaire',
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
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

        if (($filter = $this->getRequest()->query->get('filter'))
            && isset($filter['survey']['value']) && ($surveyId = $filter['survey']['value'])
        ) {
            $survey = $this->surveyRepository->find($surveyId);

            foreach ($survey->getQuestions() as $key => $surveyQuestion) {
                $listMapper->add("response_$key", null, [
                    'header_style' => 'min-width: 250px',
                    'label' => $surveyQuestion->getQuestion()->getContent(),
                    'virtual_field' => true,
                    'template' => 'admin/jecoute/data_survey/list_response.html.twig',
                ]);
            }
        }
    }

    /**
     * @required
     */
    public function setSurveyRepository(SurveyRepository $surveyRepository): void
    {
        $this->surveyRepository = $surveyRepository;
    }
}
