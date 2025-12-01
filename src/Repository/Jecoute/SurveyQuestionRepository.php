<?php

declare(strict_types=1);

namespace App\Repository\Jecoute;

use App\Entity\Jecoute\Survey;
use App\Entity\Jecoute\SurveyQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\Jecoute\SurveyQuestion>
 */
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
            ->orderBy('surveyQuestion.position', 'ASC')
            ->setParameter('survey', $survey)
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult()
        ;
    }
}
