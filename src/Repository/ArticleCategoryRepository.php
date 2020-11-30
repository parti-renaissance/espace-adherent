<?php

namespace App\Repository;

use App\Entity\ArticleCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class ArticleCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArticleCategory::class);
    }

    /**
     * @return ArticleCategory[]
     */
    public function findAll()
    {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->orderBy('c.position', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneBySlug(string $slug)
    {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->where('c.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
