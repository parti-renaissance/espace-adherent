<?php

namespace AppBundle\Repository\Jecoute;

use AppBundle\Entity\Jecoute\NationalSurvey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class NationalSurveyRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, NationalSurvey::class);
    }

    /**
     * @return NationalSurvey[]
     */
    public function findAllPublished(): array
    {
        return $this
            ->createQueryBuilder('survey')
            ->addSelect('questions')
            ->innerJoin('survey.questions', 'questions')
            ->andWhere('survey.published = true')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOnePublishedByUuid(string $uuid): ?NationalSurvey
    {
        return $this
            ->createQueryBuilder('survey')
            ->addSelect('surveyQuestion', 'question', 'choices')
            ->innerJoin('survey.questions', 'surveyQuestion')
            ->innerJoin('surveyQuestion.question', 'question')
            ->leftJoin('question.choices', 'choices')
            ->innerJoin('survey.administrator', 'administrator')
            ->andWhere('survey.uuid = :uuid')
            ->andWhere('survey.published = true')
            ->setParameter('uuid', $uuid)
            ->addOrderBy('surveyQuestion.position', 'ASC')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
