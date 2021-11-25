<?php

namespace App\Repository\Jecoute;

use App\Entity\Jecoute\Survey;
use App\Entity\Jecoute\SurveyQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

class SurveyQuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SurveyQuestion::class);
    }

    public function findForSurvey(Survey $survey): array
    {
        return $this->createQueryBuilder('surveyQuestion')
            ->addSelect('question', 'dataAnswer', 'dataSurvey', 'selectedChoice')
            ->leftJoin('surveyQuestion.question', 'question')
            ->leftJoin('surveyQuestion.dataAnswers', 'dataAnswer')
            ->leftJoin('dataAnswer.dataSurvey', 'dataSurvey')
            ->leftJoin('dataAnswer.selectedChoices', 'selectedChoice')
            ->where('surveyQuestion.survey = :survey')
            ->setParameter('survey', $survey)
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult()
        ;
    }
}
