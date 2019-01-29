<?php

namespace AppBundle\Repository\Jecoute;

use AppBundle\Entity\Jecoute\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Question::class);
    }

    public function findDataByQuestion(Question $question): array
    {
        return $this
            ->createQueryBuilder('question')
            ->select(
                'question.type',
                'choices.content',
                'COUNT(DISTINCT selectedChoices) AS choicesCount',
                'COUNT(dataAnswer.textField) AS textFieldsCount'
            )
            ->leftJoin('question.choices', 'choices')
            ->leftJoin('choices.dataAnswers', 'selectedChoices')
            ->innerJoin('question.surveys', 'surveyQuestion')
            ->leftJoin('surveyQuestion.dataAnswers', 'dataAnswer')
            ->andWhere('question = :q')
            ->setParameter('q', $question)
            ->groupBy('choices.id')
            ->getQuery()
            ->getArrayResult()
        ;
    }
}
