<?php

namespace AppBundle\Repository;

use AppBundle\Entity\OrderArticle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class OrderArticleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, OrderArticle::class);
    }

    public function findOneBySlug(string $slug): ?OrderArticle
    {
        return $this
            ->createQueryBuilder('o')
            ->select('o', 'm', 's')
            ->leftJoin('o.media', 'm')
            ->leftJoin('o.sections', 's')
            ->where('o.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findPublishedArticle(string $slug): ?OrderArticle
    {
        return $this
            ->createQueryBuilder('o')
            ->where('o.slug = :slug')
            ->andWhere('o.published = true')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findAllPublished(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.published = true')
            ->getQuery()
            ->getResult()
        ;
    }
}
