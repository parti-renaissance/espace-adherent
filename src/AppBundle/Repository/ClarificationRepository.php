<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Clarification;
use Doctrine\ORM\EntityRepository;

class ClarificationRepository extends EntityRepository
{
    public function findOneBySlug(string $slug): ?Clarification
    {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->where('c.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return Clarification[]
     */
    public function findAllPublished()
    {
        return $this->findBy(['published' => true], ['createdAt' => 'DESC']);
    }
}
