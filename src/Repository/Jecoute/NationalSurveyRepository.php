<?php

declare(strict_types=1);

namespace App\Repository\Jecoute;

use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\NationalSurvey;
use App\Entity\Jecoute\SurveyQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NationalSurveyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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
            ->addSelect('survey_question', 'question', 'choice')
            ->innerJoin('survey.questions', 'survey_question')
            ->innerJoin('survey_question.question', 'question')
            ->leftJoin('question.choices', 'choice')
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

    public function findAllPublishedWithStats(): array
    {
        return $this
            ->createQueryBuilder('survey')
            ->addSelect(\sprintf('(SELECT COUNT(q.id) FROM %s AS q WHERE q.survey = survey) AS questions_count', SurveyQuestion::class))
            ->addSelect(\sprintf('(SELECT COUNT(r.id) FROM %s AS r WHERE r.survey = survey) AS responses_count', DataSurvey::class))
            ->andWhere('survey.published = true')
            ->getQuery()
            ->getResult()
        ;
    }
}
