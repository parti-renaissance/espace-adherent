<?php

namespace AppBundle\Repository\Jecoute;

use AppBundle\Entity\Jecoute\DataAnswer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DataAnswerRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DataAnswer::class);
    }

    public function findAllBySurveyQuestion(UuidInterface $surveyQuestionUuid): array
    {
        return $this
            ->createQueryBuilder('dataAnswer')
            ->select('dataAnswer.textField', 'dataSurvey.postedAt')
            ->innerJoin('dataAnswer.surveyQuestion', 'surveyQuestion')
            ->innerJoin('dataAnswer.dataSurvey', 'dataSurvey')
            ->andWhere('surveyQuestion.uuid = :surveyQuestion')
            ->setParameter('surveyQuestion', $surveyQuestionUuid)
            ->getQuery()
            ->getResult()
        ;
    }
}
