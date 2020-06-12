<?php

namespace App\Repository\VotingPlatform;

use App\Entity\VotingPlatform\CandidateGroup;
use App\Entity\VotingPlatform\ElectionRound;
use App\VotingPlatform\Collection\CandidateGroupsCollection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class CandidateGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CandidateGroup::class);
    }

    public function findForElectionRound(ElectionRound $electionRound): CandidateGroupsCollection
    {
        return new CandidateGroupsCollection($this->createQueryBuilder('cg')
            ->addSelect('candidate')
            ->innerJoin('cg.candidates', 'candidate')
            ->innerJoin('cg.electionPool', 'pool')
            ->innerJoin('pool.electionRounds', 'round')
            ->where('round = :election_round')
            ->setParameter('election_round', $electionRound)
            ->getQuery()
            ->getResult()
        );
    }

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

    public function findOneByUuid(string $uuid): ?CandidateGroup
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }
}
