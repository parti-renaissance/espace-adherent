<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Clarification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ClarificationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Clarification::class);
    }

    public function findOneBySlug(string $slug): ?Clarification
    {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->where('c.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findPublishedClarification(string $slug): ?Clarification
    {
        return $this->createQueryBuilder('c')
            ->where('c.slug = :slug')
            ->andWhere('c.published = true')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return Clarification[]
     */
    public function findAllPublished(): array
    {
        return $this->findBy(['published' => true], ['createdAt' => 'DESC']);
    }
}
