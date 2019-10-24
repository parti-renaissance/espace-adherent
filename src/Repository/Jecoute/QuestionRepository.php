<?php

namespace AppBundle\Repository\Jecoute;

use AppBundle\Entity\Jecoute\DataAnswer;
use AppBundle\Entity\Jecoute\Question;
use AppBundle\Entity\Jecoute\Survey;
use AppBundle\Entity\Jecoute\SurveyQuestion;
use AppBundle\Jecoute\SurveyQuestionTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;

class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Question::class);
    }

    public function calculateStatistics(Survey $survey): array
    {
        return $this->createQueryBuilder('q')
            ->select(
                'sq.uuid',
                'q.content AS question_content',
                'q.type',
                'ch.content as choice_content',
                'COUNT(da1.textField) AS total_simple_field',
                sprintf('(
                    SELECT COUNT(1) FROM %s AS da2 
                    INNER JOIN da2.selectedChoices AS sc
                    WHERE sc = ch
                ) AS total_by_choice', DataAnswer::class)
            )
            ->innerJoin(SurveyQuestion::class, 'sq', Join::WITH, 'sq.question = q')
            ->leftJoin('q.choices', 'ch', Join::WITH, 'q.type != :simple_field_type')
            ->leftJoin(DataAnswer::class, 'da1', Join::WITH, 'da1.surveyQuestion = sq')
            ->where('sq.survey = :survey')
            ->setParameters([
                'survey' => $survey,
                'simple_field_type' => SurveyQuestionTypeEnum::SIMPLE_FIELD,
            ])
            ->groupBy('q.id', 'ch.id')
            ->getQuery()
            ->getArrayResult()
        ;
    }
}
