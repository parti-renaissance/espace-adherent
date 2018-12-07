<?php

namespace AppBundle\Repository;

use AppBundle\Entity\IdeasWorkshop\Idea;
use AppBundle\Entity\IdeasWorkshop\IdeaStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class IdeaRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Idea::class);
    }

    public function findIdeasByStatusThemeCategoryAndName(
        int $limit,
        int $offset,
        string $status,
        string $theme = null,
        string $category = null,
        string $name = null
    ): array {
        $qb = $this->createQueryBuilder('idea');

        $qb
            ->where('idea.status != :refusedStatus')
            ->setParameter('refusedStatus', IdeaStatusEnum::REFUSED)
            ->andwhere('LOWER(idea.status) = :status')
            ->setParameter('status', strtolower($status))
        ;

        if (!empty($name)) {
            $qb
                ->andWhere('idea.name LIKE :name')
                ->setParameter('name', '%'.($name).'%')
            ;
        }

        if ($theme) {
            $qb
                ->andWhere('idea.theme = :theme')
                ->setParameter('theme', $theme)
            ;
        }

        if ($category) {
            $qb
                ->andWhere('idea.category = :category')
                ->setParameter('category', $category)
            ;
        }

        return $qb
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
        ;
    }
}
