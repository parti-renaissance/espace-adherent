<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Article;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ArticleRepository extends EntityRepository
{
    /**
     * @param string $category
     * @param int    $page
     * @param int    $perPage
     *
     * @return Article[]|Paginator
     */
    public function findByCategoryPaginated(string $category, int $page, int $perPage)
    {
        return new Paginator(
            $this->createQueryBuilder('a')
                ->select('a', 'm', 'c')
                ->leftJoin('a.media', 'm')
                ->leftJoin('a.category', 'c')
                ->where('c.slug = :category')
                ->setParameter('category', $category)
                ->andWhere('a.published = :published')
                ->setParameter('published', true)
                ->setMaxResults($perPage)
                ->setFirstResult(($page - 1) * $perPage)
                ->getQuery()
        );
    }

    /**
     * @param string $slug
     *
     * @return Article
     */
    public function findOneBySlug(string $slug)
    {
        return $this->createQueryBuilder('a')
            ->select('a', 'm', 'c')
            ->leftJoin('a.media', 'm')
            ->leftJoin('a.category', 'c')
            ->where('a.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
