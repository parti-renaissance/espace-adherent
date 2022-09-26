<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\ArticleCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function countAllByCategory(string $category): int
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(a)')
            ->andWhere('a.published = :published')
            ->setParameter('published', true)
        ;

        if (!ArticleCategory::isDefault($category)) {
            $qb->leftJoin('a.category', 'c');
            $qb->andWhere('c.slug = :category');
            $qb->setParameter('category', $category);
        }

        return (int) $qb
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return Article[]
     */
    public function findByCategoryPaginated(
        string $category,
        int $page,
        int $perPage,
        bool $isForRenaissance = false
    ): array {
        $qb = $this->createQueryBuilder('a')
            ->select('a', 'm')
            ->leftJoin('a.media', 'm')
            ->innerJoin('a.category', 'category')
            ->addSelect('category')
            ->andWhere('a.published = :published')
            ->andWhere('category.display = :display')
            ->andWhere('a.forRenaissance = :forRenaissance')
            ->setParameter('published', true)
            ->setParameter('display', true)
            ->setParameter('forRenaissance', $isForRenaissance)
            ->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults($perPage)
            ->setFirstResult(($page - 1) * $perPage)
        ;

        if (!ArticleCategory::isDefault($category)) {
            $qb
                ->andWhere('category.slug = :category')
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

    public function findOnePublishedBySlugAndCategorySlug(string $articleSlug, string $categorySlug): ?Article
    {
        return $this->createQueryBuilder('a')
            ->select('a', 'm', 'c')
            ->leftJoin('a.media', 'm')
            ->leftJoin('a.category', 'c')
            ->where('a.slug = :articleSlug')
            ->setParameter('articleSlug', $articleSlug)
            ->andWhere('a.published = :published')
            ->setParameter('published', true)
            ->andWhere('c.slug = :categorySlug')
            ->setParameter('categorySlug', $categorySlug)
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
     * @return Article[]
     */
    public function findThreeLatestOtherThan(Article $article): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a', 'm')
            ->leftJoin('a.media', 'm')
            ->where('a.published = :published')
            ->setParameter('published', true)
            ->andWhere('a.id != :current')
            ->setParameter('current', $article->getId())
            ->orderBy('a.publishedAt', 'DESC')
        ;

        if ($article->isForRenaissance()) {
            $qb->andWhere('a.forRenaissance = 1');
        }

        return $qb
            ->setMaxResults(3)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Get the query builder to find all articles.
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
