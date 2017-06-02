<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Page;
use Doctrine\ORM\EntityRepository;

class PageRepository extends EntityRepository
{
    /**
     * @param string $slug
     *
     * @return Page
     */
    public function findOneBySlug(string $slug)
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
