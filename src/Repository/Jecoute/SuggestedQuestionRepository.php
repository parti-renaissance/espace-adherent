<?php

declare(strict_types=1);

namespace App\Repository\Jecoute;

use App\Entity\Jecoute\SuggestedQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\Jecoute\SuggestedQuestion>
 */
class SuggestedQuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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
            ->setParameters(new ArrayCollection([new Parameter('id', $id), new Parameter('true', true)]))
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
