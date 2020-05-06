<?php

namespace App\Repository;

use App\Entity\ArticleCategory;
use Doctrine\ORM\EntityRepository;

class ArticleCategoryRepository extends EntityRepository
{
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
