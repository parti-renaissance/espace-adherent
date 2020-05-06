<?php

namespace App\Repository\Jecoute;

use App\Entity\Jecoute\SuggestedQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class SuggestedQuestionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SuggestedQuestion::class);
    }

    public function findById(int $id): ?SuggestedQuestion
    {
        return $this
            ->createQueryBuilder('q')
            ->addSelect('choices')
            ->leftJoin('q.choices', 'choices')
            ->where('q.id = :id AND q.published = :true')
            ->setParameters([
                'id' => $id,
                'true' => true,
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
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
