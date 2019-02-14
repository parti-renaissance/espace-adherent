<?php

namespace AppBundle\Repository;

use AppBundle\Entity\IdeasWorkshop\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class IdeaWorkshopQuestionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Question::class);
    }

    public function getMandatoryQuestions()
    {
        return $this->createQueryBuilder('question')
            ->where('question.required = :required')
            ->setParameter('required', 1)
            ->getQuery()
            ->getResult()
        ;
    }
}
