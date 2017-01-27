<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Proposal;
use Doctrine\ORM\EntityRepository;

class ProposalRepository extends EntityRepository
{
    /**
     * @return Proposal[]
     */
    public function findAllOrderedByPosition()
    {
        return $this->createQueryBuilder('p')
            ->select('p', 't')
            ->leftJoin('p.themes', 't')
            ->orderBy('p.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $slug
     *
     * @return Proposal
     */
    public function findOneBySlug(string $slug)
    {
        return $this->createQueryBuilder('p')
            ->select('p', 'm', 't')
            ->leftJoin('p.media', 'm')
            ->leftJoin('p.themes', 't')
            ->where('p.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
