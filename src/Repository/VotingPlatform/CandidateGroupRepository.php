<?php

namespace App\Repository\VotingPlatform;

use App\Entity\VotingPlatform\CandidateGroup;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class CandidateGroupRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CandidateGroup::class);
    }

    /**
     * @return CandidateGroup[]
     */
    public function findByUuids(array $uuids): array
    {
        return $this->createQueryBuilder('cg')
            ->addSelect('candidate')
            ->innerJoin('cg.candidates', 'candidate')
            ->where('cg.uuid IN (:uuids)')
            ->setParameter('uuids', $uuids)
            ->getQuery()
            ->getResult()
        ;
    }
}
