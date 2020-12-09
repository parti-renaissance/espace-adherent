<?php

namespace App\Repository\Jecoute;

use App\Entity\Jecoute\Survey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class SurveyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Survey::class);
    }

    public function findOneByUuid(string $uuid): ?Survey
    {
        return $this
            ->createQueryBuilder('survey')
            ->addSelect('surveyQuestion', 'question', 'choices')
            ->innerJoin('survey.questions', 'surveyQuestion')
            ->innerJoin('surveyQuestion.question', 'question')
            ->leftJoin('question.choices', 'choices')
            ->andWhere('survey.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->addOrderBy('surveyQuestion.position', 'ASC')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOnePublishedByUuid(string $uuid): ?Survey
    {
        return $this
            ->createQueryBuilder('survey')
            ->addSelect('surveyQuestion', 'question', 'choices')
            ->leftJoin('survey.questions', 'surveyQuestion')
            ->leftJoin('surveyQuestion.question', 'question')
            ->leftJoin('question.choices', 'choices')
            ->andWhere('survey.uuid = :uuid')
            ->andWhere('survey.published = true')
            ->setParameter('uuid', $uuid)
            ->addOrderBy('surveyQuestion.position', 'ASC')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
