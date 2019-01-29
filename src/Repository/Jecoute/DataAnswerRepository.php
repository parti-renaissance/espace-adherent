<?php

namespace AppBundle\Repository\Jecoute;

use AppBundle\Entity\Jecoute\DataAnswer;
use AppBundle\Entity\Jecoute\SurveyQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DataAnswerRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DataAnswer::class);
    }

    public function findAllBySurveyQuestion(SurveyQuestion $surveyQuestion): array
    {
        return $this
            ->createQueryBuilder('dataAnswer')
            ->select('dataAnswer.textField', 'dataSurvey.postedAt')
            ->innerJoin('dataAnswer.surveyQuestion', 'surveyQuestion')
            ->innerJoin('dataAnswer.dataSurvey', 'dataSurvey')
            ->andWhere('surveyQuestion = :surveyQuestion')
            ->setParameter('surveyQuestion', $surveyQuestion)
            ->getQuery()
            ->getResult()
        ;
    }
}
