<?php

namespace AppBundle\Repository\Jecoute;

use AppBundle\Entity\Jecoute\SuggestedQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class SuggestedQuestionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SuggestedQuestion::class);
    }

    /**
     * @return SuggestedQuestion[]
     */
    public function findAllPublished(): array
    {
        return $this
            ->createQueryBuilder('suggestedQuestions')
            ->addSelect('choices')
            ->leftJoin('suggestedQuestions.choices', 'choices')
            ->andWhere('suggestedQuestions.published = true')
            ->getQuery()
            ->getResult()
        ;
    }
}
