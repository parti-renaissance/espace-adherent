<?php

declare(strict_types=1);

namespace App\Repository\Jecoute;

use App\Entity\Jecoute\DataAnswer;
use App\Entity\Jecoute\Question;
use App\Entity\Jecoute\Survey;
use App\Entity\Jecoute\SurveyQuestion;
use App\Jecoute\SurveyQuestionTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\Jecoute\Question>
 */
class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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
                \sprintf('(
                    SELECT COUNT(1) FROM %s AS da2
                    INNER JOIN da2.selectedChoices AS sc
                    WHERE sc = ch
                ) AS total_by_choice', DataAnswer::class)
            )
            ->innerJoin(SurveyQuestion::class, 'sq', Join::WITH, 'sq.question = q')
            ->leftJoin('q.choices', 'ch', Join::WITH, 'q.type != :simple_field_type')
            ->leftJoin(DataAnswer::class, 'da1', Join::WITH, 'da1.surveyQuestion = sq')
            ->where('sq.survey = :survey')
            ->setParameters(new ArrayCollection([new Parameter('survey', $survey), new Parameter('simple_field_type', SurveyQuestionTypeEnum::SIMPLE_FIELD)]))
            ->groupBy('q.id', 'ch.id')
            ->getQuery()
            ->getArrayResult()
        ;
    }
}
