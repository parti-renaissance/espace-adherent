<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Article;
use AppBundle\Entity\ArticleCategory;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class ArticleRepository extends EntityRepository
{
    /**
     * @param string $category
     *
     * @return int
     */
    public function countAllByCategory(string $category): int
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(a)')
            ->andWhere('a.published = :published')
            ->setParameter('published', true);

        if (!ArticleCategory::isDefault($category)) {
            $qb->leftJoin('a.category', 'c');
            $qb->andWhere('c.slug = :category');
            $qb->setParameter('category', $category);
        }

        return (int) $qb
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param string $category
     * @param int    $page
     * @param int    $perPage
     *
     * @return Article[]
     */
    public function findByCategoryPaginated(string $category, int $page, int $perPage): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a', 'm')
            ->leftJoin('a.media', 'm')
            ->andWhere('a.published = :published')
            ->setParameter('published', true)
            ->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults($perPage)
            ->setFirstResult(($page - 1) * $perPage)
        ;

        if (!ArticleCategory::isDefault($category)) {
            $qb
                ->addSelect('c')
                ->leftJoin('a.category', 'c')
                ->andWhere('c.slug = :category')
                ->setParameter('category', $category)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function findOneBySlug(string $slug): ?Article
    {
        return $this->createQueryBuilder('a')
            ->select('a', 'm', 'c')
            ->leftJoin('a.media', 'm')
            ->leftJoin('a.category', 'c')
            ->where('a.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOnePublishedBySlug(string $slug): ?Article
    {
        return $this->createQueryBuilder('a')
            ->select('a', 'm', 'c')
            ->leftJoin('a.media', 'm')
            ->leftJoin('a.category', 'c')
            ->where('a.slug = :slug')
            ->setParameter('slug', $slug)
            ->andWhere('a.published = :published')
            ->setParameter('published', true)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return Article[]
     */
    public function findAllPublished(): array
    {
        return $this->createFindAllQueryBuilder()->getQuery()->getResult();
    }

    /**
     * @return Article[]
     */
    public function findAllForFeed(): array
    {
        return $this->createFindAllQueryBuilder()
            ->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param Article $article
     *
     * @return Article[]
     */
    public function findThreeLatestOtherThan(Article $article): array
    {
        return $this->createQueryBuilder('a')
            ->select('a', 'm')
            ->leftJoin('a.media', 'm')
            ->where('a.published = :published')
            ->setParameter('published', true)
            ->andWhere('a.id != :current')
            ->setParameter('current', $article->getId())
            ->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Get the query builder to find all articles.
     *
     * @return QueryBuilder
     */
    private function createFindAllQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('a')
            ->select('a', 'm', 'c')
            ->leftJoin('a.media', 'm')
            ->leftJoin('a.category', 'c')
            ->andWhere('a.published = :published')
            ->setParameter('published', true)
        ;
    }
}
