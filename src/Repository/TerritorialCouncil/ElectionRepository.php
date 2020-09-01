<?php

namespace App\Repository\TerritorialCouncil;

use App\Entity\ReferentTag;
use App\Entity\TerritorialCouncil\Election;
use App\Entity\VotingPlatform\Designation\Designation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ElectionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Election::class);
    }

    /**
     * @param ReferentTag[] $tags
     *
     * @return Election[]
     */
    public function findAllForReferentTags(array $tags): array
    {
        return $this->createQueryBuilder('election')
            ->innerJoin('election.territorialCouncil', 'council')
            ->innerJoin('council.referentTags', 'tag')
            ->where('tag IN (:tags)')
            ->setParameter('tags', $tags)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Election[]
     */
    public function findAllByDesignation(Designation $designation, int $offset = 0, ?int $limit = 200): array
    {
        return $this->createQueryBuilder('e')
            ->addSelect('e')
            ->innerJoin('e.territorialCouncil', 'tc')
            ->where('e.designation = :designation')
            ->setParameter('designation', $designation)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
}
