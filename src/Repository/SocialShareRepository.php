<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SocialShare;
use App\Entity\SocialShareCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SocialShareRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SocialShare::class);
    }

    public function findForWall(?SocialShareCategory $category = null)
    {
        $qb = $this->createQueryBuilder('s')
            ->select('s', 'c')
            ->leftJoin('s.socialShareCategory', 'c')
            ->where('s.published = :published')
            ->setParameter('published', true)
            ->orderBy('s.position', 'ASC')
            ->addOrderBy('s.createdAt', 'DESC')
        ;

        if ($category) {
            $qb->andWhere('c = :category')->setParameter('category', $category);
        }

        return $qb->getQuery()->getResult();
    }
}
