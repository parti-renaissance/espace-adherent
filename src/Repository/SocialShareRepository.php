<?php

namespace AppBundle\Repository;

use AppBundle\Entity\SocialShareCategory;
use Doctrine\ORM\EntityRepository;

class SocialShareRepository extends EntityRepository
{
    public function findForWall(SocialShareCategory $category = null)
    {
        $qb = $this->createQueryBuilder('s')
            ->select('s', 'c')
            ->leftJoin('s.socialShareCategory', 'c')
            ->where('s.published = true')
            ->orderBy('s.position', 'ASC')
            ->addOrderBy('s.createdAt', 'DESC')
        ;

        if ($category) {
            $qb
                ->andWhere('c = :category')
                ->setParameter('category', $category)
            ;
        }

        return $qb->getQuery()->getResult();
    }
}
