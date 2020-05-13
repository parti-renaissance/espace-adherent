<?php

namespace App\Repository\Jecoute;

use App\Entity\Jecoute\Survey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class SurveyRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
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
}
