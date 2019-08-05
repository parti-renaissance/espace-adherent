<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Proposal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ProposalRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Proposal::class);
    }

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
            ->andWhere('p.published = :published')
            ->setParameters([
                'slug' => $slug,
                'published' => true,
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
