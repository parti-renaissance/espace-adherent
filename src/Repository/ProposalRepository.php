<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Proposal;
use Doctrine\ORM\EntityRepository;

class ProposalRepository extends EntityRepository
{
    /**
     * @return Proposal[]
     */
    public function findAllOrderedByPosition(): array
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p', 't')
            ->leftJoin('p.themes', 't')
            ->orderBy('p.position', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneBySlug(string $slug): ?Proposal
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p', 'm', 't')
            ->leftJoin('p.media', 'm')
            ->leftJoin('p.themes', 't')
            ->where('p.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findPublishedProposal(string $slug): ?Proposal
    {
        return $this
            ->createQueryBuilder('p')
            ->where('p.slug = :slug')
            ->andWhere('p.published = 1')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
