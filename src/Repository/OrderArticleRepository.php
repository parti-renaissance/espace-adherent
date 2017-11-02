<?php

namespace AppBundle\Repository;

use AppBundle\Entity\OrderArticle;
use Doctrine\ORM\EntityRepository;

class OrderArticleRepository extends EntityRepository
{
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
            ->andWhere('o.published = 1')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findAllPublished(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.published = :published')
            ->setParameter('published', true)
            ->getQuery()
            ->getResult()
        ;
    }
}
